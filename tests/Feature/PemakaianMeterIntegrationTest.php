<?php

namespace Tests\Feature;

use App\Models\BillingPeriod;
use App\Models\MeterReading;
use App\Models\User;
use App\Models\WaterMeter;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class PemakaianMeterIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $petugas;
    private User $pelanggan;
    private WaterMeter $meter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\AccessControlSeeder::class);
        $this->travelTo(now()->setDate(2026, 11, 16)->startOfDay());
        $this->petugas = User::factory()->create(['role' => 'petugas', 'status' => 'active']);
        $this->pelanggan = User::factory()->create(['role' => 'masyarakat', 'customer_id' => 'CUST-00002']);
        $this->meter = WaterMeter::create([
            'user_id' => $this->pelanggan->id,
            'meter_number' => 'MTR-CBD-002',
            'status' => 'active',
        ]);
        BillingPeriod::query()->update(['status' => 'closed']);
        BillingPeriod::create(['year' => 2026, 'month' => 10, 'billing_period' => '2026-10', 'status' => 'active']);
        MeterReading::create([
            'requires_review' => false,
            'water_meter_id' => $this->meter->id,
            'reading_value' => '317.00', 'previous_value' => '300.00',
            'reading_date' => '2026-09-10', 'recorded_by' => $this->petugas->id,
        ]);
    }

    private function kirim(mixed $akhir, array $tambahan = [])
    {
        return $this->actingAs($this->petugas)->from(route('petugas.meters.create', $this->meter->id))
            ->post(route('petugas.meters.store'), array_merge([
                'water_meter_id' => $this->meter->id,
                'reading_date' => '2026-10-10',
                'reading_value' => $akhir,
            ], $tambahan));
    }

    public function test_form_petugas_memakai_komponen_pbi09_dan_meter_awal_database(): void
    {
        $this->actingAs($this->petugas)->get(route('petugas.meters.create', $this->meter->id))
            ->assertOk()->assertSee('Catat Pemakaian Air')->assertSee('317,00')
            ->assertSee('data-awal="31700"', false)->assertSee('— m³')->assertSee('assets/meter.js');
    }

    public static function hasilValid(): array
    {
        return [['342,00', '25.00'], ['317.00', '0.00'], ['342.25', '25.25']];
    }

    #[DataProvider('hasilValid')]
    public function test_perhitungan_server_dipakai_untuk_pencatatan_yang_menunggu_pemeriksaan(string $akhir, string $pemakaian): void
    {
        $this->kirim($akhir, ['previous_value' => '1', 'usage_m3' => '9999', 'recorded_by' => $this->pelanggan->id])
            ->assertSessionHasNoErrors()->assertRedirect(route('petugas.meters.index'));
        $reading = $this->meter->readings()->whereDate('reading_date', '2026-10-10')->sole();
        $this->assertSame('317.00', $reading->previous_value);
        $this->assertSame(str_replace(',', '.', $akhir), $reading->reading_value);
        $this->assertSame($this->petugas->id, $reading->recorded_by);
        $this->assertTrue($reading->requires_review);
        $this->assertNull($reading->verified_at);
        $service = app(\App\Services\PemeriksaanPemakaian::class);
        $this->assertSame($pemakaian, $service->snapshot($reading)['usage_m3']);
        $this->assertDatabaseCount('water_bills', 0);
    }

    public static function inputTidakValid(): array
    {
        return [['316,99'], [''], ['abc'], ['-1'], ['342,001'], [['342']], ['1e3'], ['100000000']];
    }

    #[DataProvider('inputTidakValid')]
    public function test_input_tidak_valid_tidak_menambah_pencatatan_atau_tagihan(mixed $akhir): void
    {
        $this->kirim($akhir)->assertSessionHasErrors('reading_value');
        $this->assertDatabaseCount('meter_readings', 1);
        $this->assertDatabaseCount('water_bills', 0);
    }

    public function test_pelanggan_baru_menggunakan_meter_awal_nol(): void
    {
        $this->meter->readings()->delete();
        $this->kirim('12,00')->assertSessionHasNoErrors();
        $this->assertDatabaseHas('meter_readings', ['previous_value' => '0.00', 'reading_value' => '12.00']);
        $this->assertDatabaseCount('water_bills', 0);
        $this->assertSame('12.00', app(\App\Services\PemeriksaanPemakaian::class)->snapshot($this->meter->readings()->sole())['usage_m3']);
    }

    public function test_pencatatan_ganda_ditolak_dan_form_dinonaktifkan(): void
    {
        $this->kirim('342')->assertSessionHasNoErrors();
        $this->kirim('350')->assertSessionHasErrors('reading_value');
        $this->assertDatabaseCount('meter_readings', 2);
        $this->assertDatabaseCount('water_bills', 0);
        $this->get(route('petugas.meters.create', $this->meter->id))->assertOk()
            ->assertSee('sudah dicatat')->assertSee('disabled', false);
    }

    public function test_periode_tutup_dan_meter_nonaktif_ditolak(): void
    {
        $this->kirim('342', ['reading_date' => '2026-09-11'])->assertSessionHasErrors('reading_date');
        $this->meter->update(['status' => 'inactive']);
        $this->kirim('342')->assertSessionHasErrors('water_meter_id');
        $this->assertDatabaseCount('meter_readings', 1);
    }

    public function test_pencatatan_di_hari_terakhir_bulan_tetap_dikenali_sebagai_duplikat(): void
    {
        $this->kirim('342', ['reading_date' => '2026-10-31'])->assertSessionHasNoErrors();
        $this->kirim('350')->assertSessionHasErrors('reading_value');
        $this->get(route('petugas.meters.create', $this->meter->id))->assertSee('sudah dicatat');
        $this->assertDatabaseCount('meter_readings', 2);
    }

    public function test_tamu_dan_role_lain_tidak_dapat_mencatat(): void
    {
        $this->get(route('petugas.meters.create', $this->meter->id))->assertRedirect(route('login'));
        $this->post(route('petugas.meters.store'), [])->assertRedirect(route('login'));
        foreach (['masyarakat', 'bendahara'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get(route('petugas.meters.create', $this->meter->id))->assertForbidden();
            $this->post(route('petugas.meters.store'), [])->assertForbidden();
        }
    }

    public function test_kegagalan_penyimpanan_membatalkan_pencatatan_dan_membersihkan_foto(): void
    {
        Storage::fake('public');
        MeterReading::creating(fn () => throw new RuntimeException('Simulasi kegagalan simpan.'));
        $this->withoutExceptionHandling();
        try {
            $photo = UploadedFile::fake()->createWithContent('meter.png', base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aD1sAAAAASUVORK5CYII=',
            ));
            $this->kirim('342', ['photo' => $photo]);
            $this->fail('Operasi yang gagal harus melempar exception.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulasi kegagalan simpan.', $exception->getMessage());
        } finally {
            MeterReading::flushEventListeners();
        }
        $this->assertDatabaseCount('meter_readings', 1);
        $this->assertDatabaseCount('water_bills', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_permission_yang_dicabut_memblokir_semua_jalur_pbi09_termasuk_route_lama(): void
    {
        $role = \App\Models\Role::where('name', 'petugas')->firstOrFail();
        $role->permissions()->detach();
        $reading = $this->meter->readings()->sole();
        $this->actingAs($this->petugas);

        foreach ([
            route('petugas.meters.index'), route('petugas.meters.create', $this->meter->id),
            route('petugas.readings.show', $reading->id), route('petugas.readings.edit', $reading->id),
            route('meter.index'),
        ] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->post(route('petugas.meters.store'), [])->assertForbidden();
        $this->patch(route('petugas.readings.update', $reading->id), [])->assertForbidden();
        $this->post(route('petugas.readings.cancel', $reading->id), [])->assertForbidden();
        $this->assertDatabaseCount('meter_readings', 1);
        $this->assertDatabaseCount('water_bills', 0);
        $this->assertDatabaseCount('meter_reading_changes', 0);

        $role->permissions()->attach(\App\Models\Permission::where('name', 'field.manage')->value('id'));
        $this->get(route('petugas.meters.create', $this->meter->id))->assertOk();
    }

    public function test_permission_pengelolaan_hak_akses_tidak_melewati_batas_role(): void
    {
        $role = \App\Models\Role::where('name', 'petugas')->firstOrFail();
        $role->permissions()->attach(\App\Models\Permission::where('name', 'access.manage')->value('id'));
        $this->assertTrue($this->petugas->hasPermission('access.manage'));
        $this->actingAs($this->petugas)->get(route('bendahara.access.index'))->assertForbidden();
    }
}
