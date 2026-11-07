<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\PencatatanMeter;
use Database\Seeders\PelangganSeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PencatatanMeterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now('Asia/Jakarta')->setDate(2026, 11, 7)->startOfDay());
        $this->seed(PelangganSeeder::class);
    }

    private function budi(): Pelanggan
    {
        return Pelanggan::where('kode', 'CUST-00002')->firstOrFail();
    }

    private function kirim(mixed $akhir, array $extra = [])
    {
        return $this->from(route('meter.create', $this->budi()))->post(route('meter.store', $this->budi()),
            array_merge(['meter_akhir' => $akhir, 'periode' => '2026-11'], $extra));
    }

    public function test_daftar_dan_form_membuka_pelanggan_yang_berbeda(): void
    {
        $rina = Pelanggan::where('kode', 'CUST-00001')->firstOrFail();
        $this->get(route('meter.index'))->assertOk()->assertSee('Pencatatan Water Meter')
            ->assertSee(route('meter.create', $rina))->assertSee(route('meter.create', $this->budi()));
        $this->get(route('meter.create', $rina))->assertOk()->assertSee('Rina Marlina')
            ->assertSee('MTR-CBD-001')->assertSee('530,00')->assertDontSee('Budi Santoso');
        $this->get(route('meter.create', $this->budi()))->assertOk()->assertSee('Budi Santoso')
            ->assertSee('MTR-CBD-002')->assertSee('317,00')->assertDontSee('Rina Marlina');
    }

    public function test_pencarian_dan_filter_wilayah(): void
    {
        $this->get(route('meter.index', ['q' => 'Rina']))->assertOk()->assertSee('Rina Marlina')->assertDontSee('Budi Santoso');
        $this->get(route('meter.index', ['q' => 'MTR-CBD-002']))->assertOk()->assertSee('Budi Santoso')->assertDontSee('Rina Marlina');
        $this->get(route('meter.index', ['wilayah' => 'Dusun III - Sukamaju']))->assertOk()->assertSee('Budi Santoso')->assertDontSee('Rina Marlina');
        $this->get(route('meter.index', ['q' => 'Rina', 'wilayah' => 'Dusun III - Sukamaju']))->assertSee('Pelanggan tidak ditemukan');
    }

    public function test_317_ke_342_menyimpan_25_dari_database_dan_mengabaikan_nilai_palsu(): void
    {
        $this->kirim('342', ['meter_awal' => '1', 'pemakaian' => '9999', 'status' => 'disetujui'])
            ->assertRedirect(route('meter.index'))->assertSessionHas('success');
        $this->assertDatabaseHas('pencatatan_meter', [
            'pelanggan_id' => $this->budi()->id, 'periode' => '2026-11',
            'meter_awal_satuan' => 31700, 'meter_akhir_satuan' => 34200,
            'pemakaian_satuan' => 2500, 'status' => 'menunggu_pemeriksaan_bendahara',
        ]);
        $this->get(route('meter.index'))->assertSee('342,00')->assertSee('Menunggu pemeriksaan Bendahara');
    }

    public function test_317_ke_317_sah_dengan_hasil_nol(): void
    {
        $this->kirim('317')->assertSessionHasNoErrors()->assertRedirect(route('meter.index'));
        $this->assertDatabaseHas('pencatatan_meter', ['pemakaian_satuan' => 0]);
    }

    public static function inputTidakValid(): array
    {
        return [['316'], [''], ['abc'], ['-1'], ['317.001'], ['1e3'], [['342']], ['9999999999'], ['NaN']];
    }

    #[DataProvider('inputTidakValid')]
    public function test_input_tidak_valid_tidak_disimpan_dan_hasil_lama_kosong(mixed $akhir): void
    {
        $response = $this->followingRedirects()->kirim($akhir);
        $this->assertDatabaseCount('pencatatan_meter', 0);
        $response->assertOk()
            ->assertSee('Pencatatan belum disimpan.')
            ->assertSee('id="total-pemakaian">— m³', false)->assertSee('data-invalid="1"', false);
    }

    public function test_pencatatan_ganda_ditolak(): void
    {
        $this->kirim('342')->assertSessionHasNoErrors();
        $this->kirim('350')->assertSessionHasErrors('pencatatan');
        $this->assertDatabaseCount('pencatatan_meter', 1);
        $this->assertDatabaseHas('pencatatan_meter', ['meter_akhir_satuan' => 34200]);
        $this->get(route('meter.create', $this->budi()))->assertSee('Pelanggan ini sudah dicatat');
    }

    public function test_indeks_database_mencegah_duplikasi_langsung(): void
    {
        $this->kirim('342');
        $data = PencatatanMeter::firstOrFail()->getAttributes();
        unset($data['id']);
        $this->expectException(UniqueConstraintViolationException::class);
        PencatatanMeter::create($data);
    }

    public function test_desimal_koma_dihitung_tepat(): void
    {
        $this->kirim('342,25')->assertSessionHasNoErrors();
        $this->assertDatabaseHas('pencatatan_meter', ['pemakaian_satuan' => 2525]);
    }

    public function test_periode_sebelumnya_menjadi_meter_awal_berikutnya(): void
    {
        $this->kirim('342');
        $this->travelTo(now()->addMonth());
        $this->get(route('meter.create', $this->budi()))->assertSee('342,00');
        $this->kirim('350', ['periode' => '2026-12'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('pencatatan_meter', ['periode' => '2026-12', 'meter_awal_satuan' => 34200, 'pemakaian_satuan' => 800]);
    }

    public function test_periode_palsu_ditolak(): void
    {
        $this->kirim('342', ['periode' => '2026-10'])->assertSessionHasErrors('periode');
        $this->assertDatabaseCount('pencatatan_meter', 0);
    }

    public function test_periode_berganti_mengikuti_tengah_malam_jakarta(): void
    {
        $this->travelTo(now('Asia/Jakarta')->setDate(2026, 12, 1)->startOfDay());

        $this->kirim('342', ['periode' => '2026-12'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('pencatatan_meter', ['periode' => '2026-12']);
    }

    public function test_batal_hanya_kembali_ke_daftar_tanpa_menyimpan(): void
    {
        $this->get(route('meter.create', $this->budi()))->assertSee('href="'.route('meter.index').'">Batal', false);
        $this->get(route('meter.index'))->assertOk();
        $this->assertDatabaseCount('pencatatan_meter', 0);
    }

    public function test_pelanggan_tidak_ada_menghasilkan_404(): void
    {
        $this->get('/catat-meter/999/catat')->assertNotFound();
        $this->post('/catat-meter/999', ['meter_akhir' => '342', 'periode' => '2026-11'])->assertNotFound();
    }
}
