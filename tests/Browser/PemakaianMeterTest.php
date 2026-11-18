<?php

namespace Tests\Browser;

use App\Models\MeterReading;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\Browser\Support\TeamDuskTestCase;

#[Group('PBI-1')]
class PemakaianMeterTest extends TeamDuskTestCase
{
    private function open(Browser $b, bool $history = true): int
    {
        $meter = $this->meter($history);
        $this->login($b, $this->petugas)->visit('/petugas/meters');
        $selector = 'a[href$="/meters/'.$meter->id.'/catat"]';
        $b->clickLink('Catat', $selector)->waitForLocation('/petugas/meters/'.$meter->id.'/catat')->waitFor('#meter-akhir');
        return $meter->id;
    }

    private function saveAndAssert(Browser $b, int $meter, string $input, string $end, string $usage, string $start = '317.00'): void
    {
        $b->type('reading_value', $input)->assertSeeIn('#total-pemakaian', $usage.' m³')
            ->press('Simpan Pencatatan')->waitForLocation('/petugas/meters');
        $reading = MeterReading::where('water_meter_id', $meter)->where('reading_date', '>=', now()->startOfMonth()->toDateString())->sole();
        $this->assertSame($start, $reading->previous_value);
        $this->assertSame($end, $reading->reading_value);
        $this->assertSame($this->petugas->id, $reading->recorded_by);
        $this->assertDatabaseCount('water_bills', 0);
        $b->click('a[href$="/readings/'.$reading->id.'"]')->waitFor('#total-pemakaian')->assertSeeIn('#total-pemakaian', $usage.' m³');
    }

    public function test_tc01_meter_awal_dari_riwayat_dan_readonly(): void
    {
        $this->browse(function (Browser $b) {
            $this->open($b);
            $b->assertInputValue('#meter-awal', '317,00')->assertAttribute('#meter-awal', 'readonly', 'true')
                ->assertSeeIn('#total-pemakaian', '— m³');
            $this->evidence($b, 'PBI-1-TC01');
        });
    }

    public function test_tc02_meter_342_kurang_317_menghasilkan_25_dan_tersimpan(): void
    {
        $this->browse(function (Browser $b) {
            $meter = $this->open($b);
            $b->type('reading_value', '342,00')->assertSeeIn('#rumus', '342,00 − 317,00 = 25,00 m³');
            $this->saveAndAssert($b, $meter, '342,00', '342.00', '25,00');
            $this->evidence($b, 'PBI-1-TC02');
        });
    }

    public function test_tc03_meter_sama_menghasilkan_nol_dan_tersimpan(): void
    {
        $this->browse(function (Browser $b) {
            $meter = $this->open($b);
            $this->saveAndAssert($b, $meter, '317,00', '317.00', '0,00');
            $this->evidence($b, 'PBI-1-TC03');
        });
    }

    private function reject(Browser $b, string $input, string $message): void
    {
        $count = MeterReading::count();
        $b->type('reading_value', $input)->assertSeeIn('#total-pemakaian', '— m³')->assertSeeIn('#meter-error', $message)
            ->press('Simpan Pencatatan')->waitForText('Pencatatan belum disimpan.')
            ->assertSeeIn('#total-pemakaian', '— m³');
        $this->assertSame($count, MeterReading::count());
    }

    public function test_tc04_meter_lebih_kecil_ditolak_client_dan_server(): void
    {
        $this->browse(function (Browser $b) {
            $this->open($b);
            $this->reject($b, '300,00', 'Meter akhir tidak boleh lebih kecil dari meter awal.');
            $b->assertSee('Meter akhir tidak boleh lebih kecil dari meter awal.');
            $this->evidence($b, 'PBI-1-TC04');
        });
    }

    public function test_tc05_meter_kosong_tidak_tersimpan(): void
    {
        $this->browse(function (Browser $b) {
            $this->open($b);
            $count = MeterReading::count();
            $b->press('Simpan Pencatatan')->assertSeeIn('#total-pemakaian', '— m³')
                ->assertScript('document.querySelector("#meter-akhir").validity.valueMissing', true);
            $this->assertSame($count, MeterReading::count());
            $this->evidence($b, 'PBI-1-TC05');
        });
    }

    public function test_tc06_huruf_ditolak_client_dan_server(): void
    {
        $this->browse(function (Browser $b) {
            $this->open($b);
            $this->reject($b, 'abc', 'Isi angka positif atau nol');
            $this->evidence($b, 'PBI-1-TC06');
        });
    }

    public function test_tc07_koma_dan_titik_menghasilkan_desimal_yang_sama(): void
    {
        $this->browse(function (Browser $b) {
            foreach (['342,25', '342.25'] as $i => $input) {
                // Meter berbeda sehingga tidak berbenturan dengan pencatatan bulanan pertama.
                $meter = $this->open($b);
                $this->saveAndAssert($b, $meter, $input, '342.25', '25,25');
                $this->evidence($b, 'PBI-1-TC07-'.($i + 1));
            }
        });
    }

    public function test_tc08_lebih_dua_desimal_dan_melebihi_batas_ditolak(): void
    {
        $this->browse(function (Browser $b) {
            $this->open($b);
            $this->reject($b, '342,001', 'Isi angka positif atau nol');
            $b->refresh();
            $this->reject($b, '100000000', 'Meter akhir maksimal 8 digit');
            $this->evidence($b, 'PBI-1-TC08');
        });
    }

    public function test_tc09_pelanggan_baru_meter_awal_nol(): void
    {
        $this->browse(function (Browser $b) {
            $meter = $this->open($b, false);
            $b->assertInputValue('#meter-awal', '0,00');
            $this->saveAndAssert($b, $meter, '12,00', '12.00', '12,00', '0.00');
            $this->evidence($b, 'PBI-1-TC09');
        });
    }
}
