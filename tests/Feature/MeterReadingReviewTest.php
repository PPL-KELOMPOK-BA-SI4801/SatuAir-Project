<?php

namespace Tests\Feature;

use App\Models\BillingPeriod;
use App\Models\MeterReading;
use App\Models\MeterReadingReview;
use App\Models\Role;
use App\Models\User;
use App\Models\WaterBill;
use App\Models\WaterMeter;
use App\Services\BillingService;
use App\Services\KoreksiPencatatanMeter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class MeterReadingReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $bendahara;
    private User $petugas;
    private User $resident;
    private WaterMeter $meter;
    private MeterReading $reading;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\AccessControlSeeder::class);
        $this->travelTo(now()->setDate(2026, 11, 18)->startOfDay());
        $this->bendahara = User::factory()->create(['role' => 'bendahara', 'status' => 'active']);
        $this->petugas = User::factory()->create(['role' => 'petugas', 'status' => 'active']);
        $this->resident = User::factory()->create(['role' => 'masyarakat', 'customer_id' => 'CUST-TEST']);
        $this->meter = WaterMeter::create(['user_id' => $this->resident->id, 'meter_number' => 'MTR-REVIEW', 'status' => 'active']);
        BillingPeriod::query()->update(['status' => 'closed']);
        BillingPeriod::create(['year' => 2026, 'month' => 11, 'billing_period' => '2026-11', 'status' => 'active']);
        MeterReading::create(['water_meter_id' => $this->meter->id, 'previous_value' => '300.00', 'reading_value' => '317.00', 'reading_date' => '2026-10-10', 'requires_review' => false]);
        $this->actingAs($this->petugas)->post(route('petugas.meters.store'), [
            'water_meter_id' => $this->meter->id, 'reading_value' => '342,00', 'reading_date' => '2026-11-10',
        ])->assertSessionHasNoErrors();
        $this->reading = $this->meter->readings()->latest('id')->firstOrFail();
        $this->assertDatabaseCount('water_bills', 0);
    }

    private function draft(array $extra = []): MeterReadingReview
    {
        $this->actingAs($this->bendahara)->post(route('bendahara.reviews.store', $this->reading), array_replace([
            'revision' => $this->reading->fresh()->reviewRevision(),
            'checks' => ['identity_period' => '1', 'meter_usage' => '1'], 'notes' => 'Angka sesuai bukti meter.',
        ], $extra))->assertSessionHasNoErrors();
        return MeterReadingReview::latest('id')->firstOrFail();
    }

    private function finalize(MeterReadingReview $review, string $decision = 'approved', ?string $revision = null)
    {
        return $this->actingAs($this->bendahara)->from(route('bendahara.reviews.show', $review))
            ->post(route('bendahara.reviews.finalize', $review), [
                'revision' => $revision ?? $review->fresh()->revision(), 'decision' => $decision,
            ]);
    }

    private function correct(): void
    {
        $this->actingAs($this->petugas)->patch(route('petugas.readings.update', $this->reading), [
            'reading_value' => '347,00', 'reason' => 'Koreksi sesuai pemeriksaan Bendahara.',
            'revision' => app(KoreksiPencatatanMeter::class)->revision($this->reading->fresh()),
        ])->assertSessionHasNoErrors();
    }

    public function test_crud_draf_menyimpan_field_tanpa_menghapus_pencatatan(): void
    {
        $this->actingAs($this->bendahara)->get(route('bendahara.reviews.index'))->assertOk()->assertSee('MTR-REVIEW');
        $this->get(route('bendahara.reviews.reading', $this->reading))->assertOk()->assertSee('25,00 m³')->assertSee('Buat Pemeriksaan');
        $this->get(route('bendahara.reviews.create', $this->reading))->assertOk()->assertSee('Checklist Pemeriksaan');
        $review = $this->draft();
        $this->assertSame($this->bendahara->id, $review->reviewer_id);
        $this->assertSame('draft', $review->status);
        $this->assertSame('25.00', $review->snapshot['usage_m3']);
        $this->get(route('bendahara.reviews.show', $review))->assertOk()->assertSee('Edit Draf')->assertSee('Angka sesuai bukti meter.');
        $this->get(route('bendahara.reviews.edit', $review))->assertOk();
        $this->patch(route('bendahara.reviews.update', $review), ['revision' => $review->revision(), 'notes' => 'Catatan hasil pemeriksaan diperbarui.'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Catatan hasil pemeriksaan diperbarui.', $review->fresh()->notes);
        $this->assertFalse($review->fresh()->checks['meter_usage']);
        $this->delete(route('bendahara.reviews.destroy', $review), ['revision' => $review->fresh()->revision()])->assertSessionHasNoErrors();
        $this->assertSoftDeleted($review);
        $this->assertNotSoftDeleted($this->reading);
        $this->assertDatabaseCount('water_bills', 0);
        $this->get(route('bendahara.reviews.reading', $this->reading))->assertOk()->assertSee('Draf dibatalkan')->assertSee('Buat Pemeriksaan');
        $this->get(route('bendahara.reviews.show', $review->id))->assertOk()->assertDontSee('Edit Draf');
        $this->draft();
        $this->assertSame(2, MeterReadingReview::withTrashed()->count());
    }

    public function test_persetujuan_menerbitkan_satu_tagihan_dan_mengunci_pencatatan(): void
    {
        $review = $this->draft();
        $old = $review->revision();
        $this->finalize($review)->assertSessionHasNoErrors();
        $this->assertSame('approved', $review->fresh()->status);
        $this->assertNotNull($this->reading->fresh()->verified_at);
        $bill = WaterBill::sole();
        $this->assertSame($this->reading->id, $bill->meter_reading_id);
        $this->assertSame('25.00', $bill->usage_m3);
        $this->assertSame('127500.00', $bill->total_amount);
        $this->assertSame($this->bendahara->id, $bill->created_by);
        $this->get(route('bendahara.reviews.show', $review))->assertOk()->assertSee('Tagihan #'.$bill->id)->assertDontSee('Edit Draf');
        $this->finalize($review, 'approved', $old)->assertSessionHasErrors('review');
        $this->delete(route('bendahara.reviews.destroy', $review), ['revision' => $review->fresh()->revision()])->assertSessionHasErrors('review');
        $this->assertDatabaseCount('water_bills', 1);
        $this->actingAs($this->petugas)->patch(route('petugas.readings.update', $this->reading), [
            'reading_value' => '350', 'reason' => 'Tidak boleh setelah disetujui.',
            'revision' => app(KoreksiPencatatanMeter::class)->revision($this->reading->fresh()),
        ])->assertSessionHasErrors('reading');
        $this->assertSame('342.00', $this->reading->fresh()->reading_value);
    }

    public static function usages(): array { return [['317.00', '0.00'], ['342.25', '25.25']]; }

    #[DataProvider('usages')]
    public function test_persetujuan_memakai_rumus_pbi09_untuk_nol_dan_desimal(string $end, string $usage): void
    {
        $this->reading->update(['reading_value' => $end]);
        $review = $this->draft();
        $this->finalize($review)->assertSessionHasNoErrors();
        $this->assertSame($usage, WaterBill::sole()->usage_m3);
    }

    public function test_pengembalian_wajib_alasan_lalu_koreksi_dan_periksa_ulang(): void
    {
        $review = $this->draft(['notes' => '', 'checks' => []]);
        $this->finalize($review, 'returned')->assertSessionHasErrors('notes');
        $this->patch(route('bendahara.reviews.update', $review), [
            'revision' => $review->fresh()->revision(), 'notes' => 'Angka foto berbeda. Mohon koreksi meter akhir.',
        ])->assertSessionHasNoErrors();
        $this->finalize($review, 'returned')->assertSessionHasNoErrors();
        $this->assertSame('returned', $review->fresh()->status);
        $this->assertDatabaseCount('water_bills', 0);
        $this->actingAs($this->petugas)->get(route('petugas.readings.show', $this->reading))->assertSee('Angka foto berbeda.');
        $this->actingAs($this->bendahara)->post(route('bendahara.reviews.store', $this->reading), [
            'revision' => $this->reading->fresh()->reviewRevision(),
        ])->assertSessionHasErrors('review');
        $this->correct();
        $this->assertDatabaseCount('water_bills', 0);
        $new = $this->draft();
        $this->finalize($new)->assertSessionHasNoErrors();
        $this->assertSame('returned', $review->fresh()->status);
        $this->assertSame('30.00', WaterBill::sole()->usage_m3);
        $this->assertSame('25.00', $review->fresh()->snapshot['usage_m3']);
    }

    public function test_koreksi_setelah_draf_membatalkan_validitas_persetujuan_lama(): void
    {
        $review = $this->draft();
        $old = $review->revision();
        $this->correct();
        $this->finalize($review, 'approved', $old)->assertSessionHasErrors('revision');
        $this->finalize($review)->assertSessionHasErrors('revision');
        $this->get(route('bendahara.reviews.edit', $review))->assertOk()->assertSee('Pencatatan telah dikoreksi');
        $this->patch(route('bendahara.reviews.update', $review), [
            'revision' => $review->fresh()->revision(), 'checks' => ['identity_period' => 1, 'meter_usage' => 1], 'notes' => 'Diperiksa ulang setelah koreksi.',
        ])->assertSessionHasNoErrors();
        $this->finalize($review)->assertSessionHasNoErrors();
        $this->assertSame('30.00', WaterBill::sole()->usage_m3);
    }

    public function test_checklist_dan_status_kiriman_palsu_tidak_menghasilkan_persetujuan(): void
    {
        $review = $this->draft(['checks' => [], 'status' => 'approved', 'reviewer_id' => $this->petugas->id, 'snapshot' => ['usage_m3' => '9999']]);
        $this->assertSame('draft', $review->status);
        $this->assertSame($this->bendahara->id, $review->reviewer_id);
        $this->assertSame('25.00', $review->snapshot['usage_m3']);
        $this->finalize($review)->assertSessionHasErrors('checks');
        $this->assertDatabaseCount('water_bills', 0);
        $this->assertNull($this->reading->fresh()->verified_at);
    }

    public function test_draf_ganda_dan_tab_lama_ditolak(): void
    {
        $review = $this->draft();
        $old = $review->revision();
        $this->post(route('bendahara.reviews.store', $this->reading), ['revision' => $this->reading->reviewRevision()])->assertSessionHasErrors('review');
        $this->patch(route('bendahara.reviews.update', $review), ['revision' => $old, 'notes' => 'Perubahan pertama.'])->assertSessionHasNoErrors();
        $this->patch(route('bendahara.reviews.update', $review), ['revision' => $old, 'notes' => 'Tab lama.'])->assertSessionHasErrors('revision');
        $this->assertSame('Perubahan pertama.', $review->fresh()->notes);
        $this->assertDatabaseCount('meter_reading_reviews', 1);
    }

    public function test_tagihan_tidak_bisa_dibuat_langsung_tanpa_keputusan_pemeriksaan(): void
    {
        $service = app(BillingService::class);
        $this->assertNull($service->createBillFromReading($this->reading, $this->petugas->id));
        $this->reading->forceFill(['verified_at' => now()])->save();
        $this->assertNull($service->createBillFromReading($this->reading, $this->bendahara->id));
        $this->actingAs($this->bendahara)->put(route('bendahara.pengguna.update', $this->resident), ['current_reading' => '900', 'tarif_per_m3' => '5000'])->assertSessionHasErrors('current_reading');
        $this->assertSame('342.00', $this->reading->fresh()->reading_value);
        $this->assertDatabaseCount('water_bills', 0);
    }

    public function test_pencatatan_pending_belum_menampilkan_nominal_tagihan_di_pembayaran(): void
    {
        $this->actingAs($this->bendahara)->get(route('bendahara.pembayaran', ['tahun' => 2026, 'bulan' => 11]))
            ->assertOk()->assertSee('Menunggu pemeriksaan')
            ->assertViewHas('warga', fn ($rows) => $rows->sole()->amount === null && $rows->sole()->bill === null);
        $this->assertDatabaseCount('water_bills', 0);
    }

    public function test_tagihan_lama_yang_sudah_ada_tidak_diganti_oleh_persetujuan(): void
    {
        $review = $this->draft();
        $bill = WaterBill::create([
            'user_id' => $this->resident->id, 'water_meter_id' => $this->meter->id,
            'billing_period' => '2026-11', 'previous_reading' => '317', 'current_reading' => '342',
            'usage_m3' => '25', 'total_amount' => '127500', 'due_date' => '2026-11-30', 'year' => 2026,
        ]);
        $this->finalize($review)->assertSessionHasErrors('review');
        $this->assertSame('draft', $review->fresh()->status);
        $this->assertNull($this->reading->fresh()->verified_at);
        $this->assertSame('127500.00', $bill->fresh()->total_amount);
        $this->assertDatabaseCount('water_bills', 1);
    }

    public function test_kegagalan_tagihan_me_rollback_keputusan_dan_verified_at(): void
    {
        $review = $this->draft();
        $this->mock(BillingService::class)->shouldReceive('createBillFromReading')->once()->andThrow(new RuntimeException('Simulasi gagal terbit.'));
        $this->withoutExceptionHandling();
        try {
            $this->finalize($review);
            $this->fail('Harus gagal.');
        } catch (RuntimeException $error) {
            $this->assertSame('Simulasi gagal terbit.', $error->getMessage());
        }
        $this->assertSame('draft', $review->fresh()->status);
        $this->assertNull($this->reading->fresh()->verified_at);
        $this->assertDatabaseCount('water_bills', 0);
    }

    public function test_role_permission_dan_pemilik_draf_diperiksa_server(): void
    {
        $review = $this->draft();
        auth()->forgetGuards();
        $this->get(route('bendahara.reviews.index'))->assertRedirect(route('login'));
        foreach ([$this->petugas, $this->resident] as $user) {
            $this->actingAs($user)->get(route('bendahara.reviews.show', $review))->assertForbidden();
            $this->post(route('bendahara.reviews.finalize', $review), [])->assertForbidden();
        }
        $other = User::factory()->create(['role' => 'bendahara', 'status' => 'active']);
        $this->actingAs($other)->get(route('bendahara.reviews.show', $review))->assertOk();
        $this->patch(route('bendahara.reviews.update', $review), ['revision' => $review->revision()])->assertForbidden();
        $this->delete(route('bendahara.reviews.destroy', $review), ['revision' => $review->revision()])->assertForbidden();
        $this->post(route('bendahara.reviews.finalize', $review), ['revision' => $review->revision(), 'decision' => 'approved'])->assertForbidden();
        Role::where('name', 'bendahara')->firstOrFail()->permissions()->detach();
        $this->actingAs($this->bendahara)->get(route('bendahara.reviews.index'))->assertForbidden();
        $this->post(route('bendahara.reviews.store', $this->reading), [])->assertForbidden();
        $this->assertSame('draft', $review->fresh()->status);
    }

    public static function blocked(): array { return [['closed'], ['inactive'], ['cancelled'], ['legacy'], ['previous_changed'], ['previous_pending']]; }

    #[DataProvider('blocked')]
    public function test_persetujuan_menolak_kondisi_pencatatan_tidak_layak(string $condition): void
    {
        $review = $this->draft();
        match ($condition) {
            'closed' => BillingPeriod::where('billing_period', '2026-11')->update(['status' => 'closed']),
            'inactive' => $this->meter->update(['status' => 'inactive']),
            'cancelled' => $this->reading->delete(),
            'legacy' => $this->reading->update(['requires_review' => false]),
            'previous_changed' => $this->meter->readings()->oldest('id')->first()->update(['reading_value' => '318']),
            'previous_pending' => $this->meter->readings()->oldest('id')->first()->update(['requires_review' => true]),
        };
        $this->finalize($review)->assertSessionHasErrors();
        $this->assertDatabaseCount('water_bills', 0);
        $this->assertSame('draft', $review->fresh()->status);
    }
}
