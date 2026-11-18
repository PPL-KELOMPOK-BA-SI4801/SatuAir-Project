<?php

namespace Tests\Feature;

use App\Models\BillingPeriod;
use App\Models\MeterReading;
use App\Models\MeterReadingChange;
use App\Models\Payment;
use App\Models\User;
use App\Models\WaterBill;
use App\Models\WaterMeter;
use App\Services\BillingService;
use App\Services\KoreksiPencatatanMeter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class MeterReadingCorrectionTest extends TestCase
{
    use RefreshDatabase;

    private User $petugas;

    private User $resident;

    private WaterMeter $meter;

    private MeterReading $reading;

    private WaterBill $bill;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\AccessControlSeeder::class);
        $this->travelTo(now()->setDate(2026, 11, 16)->startOfDay());
        $this->petugas = User::factory()->create(['role' => 'petugas', 'status' => 'active']);
        $this->resident = User::factory()->create(['role' => 'masyarakat', 'customer_id' => 'CUST-00002']);
        $this->meter = WaterMeter::create(['user_id' => $this->resident->id, 'meter_number' => 'MTR-CBD-002', 'status' => 'active']);
        BillingPeriod::query()->update(['status' => 'closed']);
        BillingPeriod::create(['year' => 2026, 'month' => 11, 'billing_period' => '2026-11', 'status' => 'active']);
        MeterReading::create([
            'water_meter_id' => $this->meter->id, 'reading_value' => '317.00', 'previous_value' => '300.00',
            'reading_date' => '2026-10-10', 'recorded_by' => $this->petugas->id,
        ]);
        $this->actingAs($this->petugas)->post(route('petugas.meters.store'), [
            'water_meter_id' => $this->meter->id, 'reading_value' => '342,00', 'reading_date' => '2026-11-10',
            'notes' => 'Catatan asli PBI-08.',
        ])->assertSessionHasNoErrors();
        $this->reading = $this->meter->readings()->whereDate('reading_date', '2026-11-10')->sole();
        // Pertahankan coverage koreksi tagihan lama; pencatatan baru PBI-10 diuji terpisah.
        $this->reading->update(['requires_review' => false]);
        app(BillingService::class)->createBillFromReading($this->reading, $this->petugas->id);
        $this->bill = WaterBill::query()->sole();
    }

    private function payload(array $extra = []): array
    {
        return array_merge([
            'reading_value' => '347,00', 'reason' => 'Koreksi sesuai foto meter.',
            'revision' => app(KoreksiPencatatanMeter::class)->revision($this->reading->fresh()),
        ], $extra);
    }

    private function update(array $extra = [])
    {
        return $this->actingAs($this->petugas)->from(route('petugas.readings.edit', $this->reading->id))
            ->patch(route('petugas.readings.update', $this->reading->id), $this->payload($extra));
    }

    private function cancel(array $extra = [])
    {
        return $this->actingAs($this->petugas)->from(route('petugas.readings.show', $this->reading->id))
            ->post(route('petugas.readings.cancel', $this->reading->id), $this->payload($extra));
    }

    public function test_rincian_dan_edit_memakai_data_pbi08_dan_perhitungan_pbi09(): void
    {
        $this->get(route('petugas.meters.index'))->assertOk()->assertSee(route('petugas.readings.show', $this->reading->id));
        $this->get(route('petugas.meters.create', $this->meter->id))->assertSee('Lihat rincian pencatatan')->assertSee('disabled', false);
        $this->get(route('petugas.readings.show', $this->reading->id))->assertOk()->assertSee('25,00 m³')
            ->assertSee('342,00 − 317,00 = 25,00 m³')->assertSee('Catatan asli PBI-08.');
        $this->get(route('petugas.readings.edit', $this->reading->id))->assertOk()->assertSee('data-awal="31700"', false);
    }

    public static function validReadings(): array
    {
        return [['347,00', '347.00', '30.00', '152500.00'], ['317,00', '317.00', '0.00', '2500.00'], ['342.25', '342.25', '25.25', '128750.00']];
    }

    #[DataProvider('validReadings')]
    public function test_koreksi_menghitung_ulang_tagihan_dan_mencatat_audit(string $input, string $end, string $usage, string $total): void
    {
        $this->update(['reading_value' => $input, 'previous_value' => '1', 'recorded_by' => $this->resident->id, 'reading_date' => '2026-01-01'])
            ->assertSessionHasNoErrors()->assertRedirect(route('petugas.readings.show', $this->reading->id));
        $reading = $this->reading->fresh();
        $bill = $this->bill->fresh();
        $this->assertSame($end, $reading->reading_value);
        $this->assertSame('317.00', $reading->previous_value);
        $this->assertSame('2026-11-10', $reading->reading_date->toDateString());
        $this->assertSame($this->petugas->id, $reading->recorded_by);
        $this->assertSame('Catatan asli PBI-08.', $reading->notes);
        $this->assertSame($usage, $bill->usage_m3);
        $this->assertSame($total, $bill->total_amount);
        $change = MeterReadingChange::query()->sole();
        $this->assertSame('342.00', $change->before['reading_value']);
        $this->assertSame($end, $change->after['reading_value']);
        $this->assertSame($this->petugas->id, $change->changed_by);
        $this->assertSame('Koreksi sesuai foto meter.', $change->reason);
        $this->assertDatabaseCount('meter_readings', 2);
        $this->assertDatabaseCount('water_bills', 1);
    }

    public function test_tarif_dan_biaya_admin_yang_sudah_diatur_tidak_diganti(): void
    {
        $this->bill->update(['rate_per_m3' => '6500.25', 'admin_fee' => '3000.50']);
        $this->update(['reading_value' => '342.25'])->assertSessionHasNoErrors();
        $this->assertSame('6500.25', $this->bill->fresh()->rate_per_m3);
        $this->assertSame('3000.50', $this->bill->fresh()->admin_fee);
        $this->assertSame('167131.81', $this->bill->fresh()->total_amount);
    }

    public static function invalidReadings(): array
    {
        return [['316,99'], [''], ['abc'], ['-1'], ['347,001'], [['347']], ['100000000'], ['342,00']];
    }

    #[DataProvider('invalidReadings')]
    public function test_koreksi_tidak_valid_tidak_mengubah_database(mixed $input): void
    {
        $this->update(['reading_value' => $input])->assertSessionHasErrors('reading_value');
        $this->assertUnchanged();
    }

    public function test_alasan_kosong_dan_revision_palsu_ditolak(): void
    {
        $this->update(['reason' => '   '])->assertSessionHasErrors('reason');
        $this->cancel(['reason' => ''])->assertSessionHasErrors('reason');
        $this->update(['revision' => str_repeat('0', 64)])->assertSessionHasErrors('revision');
        $this->cancel(['revision' => str_repeat('0', 64)])->assertSessionHasErrors('revision');
        $this->assertUnchanged();
    }

    public function test_tab_lama_tidak_dapat_menimpa_perubahan_baru_meski_pada_detik_yang_sama(): void
    {
        $old = $this->payload();
        $this->update()->assertSessionHasNoErrors();
        $this->patch(route('petugas.readings.update', $this->reading->id), $old)->assertSessionHasErrors('revision');
        $this->post(route('petugas.readings.cancel', $this->reading->id), $old)->assertSessionHasErrors('revision');
        $this->assertSame('347.00', $this->reading->fresh()->reading_value);
        $this->assertDatabaseCount('meter_reading_changes', 1);
    }

    public function test_pembatalan_mempertahankan_riwayat_mengembalikan_meter_awal_dan_boleh_mencatat_ulang(): void
    {
        $this->cancel(['reason' => 'Salah pelanggan.'])->assertSessionHasNoErrors();
        $this->assertSoftDeleted($this->reading);
        $this->assertSoftDeleted($this->bill);
        $this->assertSame(0, WaterBill::query()->count());
        $this->assertSame('317.00', $this->meter->fresh()->latestReading->reading_value);
        $this->assertSame('317.00', $this->meter->readingBeforePeriod('2026-12')->reading_value);
        $this->get(route('petugas.meters.index'))->assertOk()->assertSee('Pencatatan terakhir dibatalkan');
        $this->get(route('petugas.readings.show', $this->reading->id))->assertOk()->assertSee('Salah pelanggan.')->assertSee('Dibatalkan');
        $this->get(route('petugas.readings.edit', $this->reading->id))->assertOk()->assertDontSee('data-correction="1"', false);
        $this->actingAs($this->resident)->get(route('masyarakat.bills.show', $this->bill->id))->assertNotFound();
        $this->post(route('masyarakat.payments.store'), ['water_bill_id' => $this->bill->id, 'payment_method' => 'qris'])->assertNotFound();
        $this->actingAs($this->petugas)->post(route('petugas.meters.store'), [
            'water_meter_id' => $this->meter->id, 'reading_value' => '342,00', 'reading_date' => '2026-11-11',
        ])->assertSessionHasNoErrors();
        $this->assertSame(2, MeterReading::query()->count());
        $this->assertSame(3, MeterReading::withTrashed()->count());
        $this->assertSame(0, WaterBill::query()->count());
        $replacement = $this->meter->readings()->whereDate('reading_date', '2026-11-11')->sole();
        $this->get(route('petugas.readings.show', $replacement->id))->assertOk()
            ->assertSee(route('petugas.readings.show', $this->reading->id));
        $this->assertDatabaseCount('meter_reading_changes', 1);
        $this->assertSame('cancelled', MeterReadingChange::query()->sole()->action);
        $this->cancel()->assertSessionHasErrors('reading');
    }

    public static function lockedConditions(): array
    {
        return [['closed'], ['inactive'], ['verified'], ['next_reading'], ['next_bill'], ['duplicate'], ['paid'], ['payment_pending'], ['payment_verified'], ['payment_rejected'], ['payment_cancelled']];
    }

    #[DataProvider('lockedConditions')]
    public function test_kondisi_terkunci_diperiksa_ulang_di_server(string $condition): void
    {
        match ($condition) {
            'closed' => BillingPeriod::query()->update(['status' => 'closed']),
            'inactive' => $this->meter->update(['status' => 'inactive']),
            'verified' => $this->reading->forceFill(['verified_at' => now()])->save(),
            'next_reading', 'duplicate' => MeterReading::create([
                'water_meter_id' => $this->meter->id, 'reading_value' => '350', 'previous_value' => '342',
                'reading_date' => $condition === 'duplicate' ? '2026-11-11' : '2026-12-10',
            ]),
            'next_bill' => $this->bill->replicate(['meter_reading_id'])->fill(['billing_period' => '2026-12'])->save(),
            'paid' => $this->bill->update(['status' => 'paid']),
            default => Payment::create([
                'water_bill_id' => $this->bill->id, 'user_id' => $this->resident->id,
                'amount' => $this->bill->total_amount, 'payment_method' => 'qris',
                'status' => substr($condition, strlen('payment_')),
            ]),
        };
        $this->update()->assertSessionHasErrors('reading');
        $this->cancel()->assertSessionHasErrors('reading');
        $this->get(route('petugas.readings.edit', $this->reading->id))->assertOk()->assertDontSee('data-correction="1"', false);
        $this->assertUnchanged($condition !== 'paid');
    }

    public function test_akses_hanya_untuk_petugas(): void
    {
        auth()->forgetGuards();
        $this->get(route('petugas.readings.show', $this->reading->id))->assertRedirect(route('login'));
        foreach (['masyarakat', 'bendahara'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->get(route('petugas.readings.show', $this->reading->id))->assertForbidden();
            $this->get(route('petugas.readings.edit', $this->reading->id))->assertForbidden();
            $this->patch(route('petugas.readings.update', $this->reading->id), $this->payload())->assertForbidden();
            $this->post(route('petugas.readings.cancel', $this->reading->id), $this->payload())->assertForbidden();
        }
        $this->assertUnchanged();
    }

    public function test_kegagalan_tagihan_me_rollback_koreksi_dan_audit(): void
    {
        $this->mock(BillingService::class)->shouldReceive('createBillFromReading')->once()->andThrow(new RuntimeException('Simulasi tagihan gagal.'));
        $this->withoutExceptionHandling();
        try {
            $this->update();
            $this->fail('Harus gagal.');
        } catch (RuntimeException $error) {
            $this->assertSame('Simulasi tagihan gagal.', $error->getMessage());
        }
        $this->assertUnchanged();
    }

    public function test_kegagalan_audit_me_rollback_pembatalan_dan_tagihan(): void
    {
        MeterReadingChange::creating(fn () => throw new RuntimeException('Simulasi audit gagal.'));
        $this->withoutExceptionHandling();
        try {
            $this->cancel();
            $this->fail('Harus gagal.');
        } catch (RuntimeException $error) {
            $this->assertSame('Simulasi audit gagal.', $error->getMessage());
        } finally {
            MeterReadingChange::flushEventListeners();
        }
        $this->assertUnchanged();
    }

    private function assertUnchanged(bool $unpaid = true): void
    {
        $this->assertSame('342.00', $this->reading->fresh()->reading_value);
        $this->assertSame('25.00', $this->bill->fresh()->usage_m3);
        $this->assertFalse($this->reading->fresh()->trashed());
        $this->assertFalse($this->bill->fresh()->trashed());
        if ($unpaid) {
            $this->assertSame('unpaid', $this->bill->fresh()->status);
        }
        $this->assertDatabaseCount('meter_reading_changes', 0);
    }
}
