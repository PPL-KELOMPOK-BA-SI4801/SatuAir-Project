<?php

namespace Tests\Browser;

use App\Models\MeterReading;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\Browser\Support\TeamDuskTestCase;

#[Group('PBI-3')]
class RiwayatMeterTest extends TeamDuskTestCase
{
    public function test_tc01_riwayat_ringkasan_dan_data_hanya_milik_pelanggan_login(): void
    {
        $meter = $this->meter();
        MeterReading::create(['water_meter_id' => $meter->id, 'previous_value' => '317.00', 'reading_value' => '342.00',
            'reading_date' => now()->toDateString(), 'recorded_by' => $this->petugas->id, 'requires_review' => false]);
        $other = $this->meter(false, $this->rina);
        MeterReading::create(['water_meter_id' => $other->id, 'previous_value' => '0.00', 'reading_value' => '999.00',
            'reading_date' => now()->toDateString(), 'recorded_by' => $this->petugas->id, 'requires_review' => false]);
        $this->browse(function (Browser $b) use ($meter, $other) {
            $this->login($b, $this->budi)->clickLink('Riwayat Meter')->waitForText('Daftar Riwayat Pencatatan')
                ->assertSeeIn('@history-count', '2')->assertSeeIn('@history-usage', '42,00 m³')
                ->assertElementsCount('tbody tr', 2)->assertSeeIn('tbody', $meter->meter_number)
                ->assertSeeIn('tbody', '317.00')->assertSeeIn('tbody', '342.00')->assertSeeIn('tbody', '25,00 m³')
                ->assertDontSeeIn('tbody', $other->meter_number)->assertDontSeeIn('tbody', '999.00');
            $this->evidence($b, 'PBI-3-TC01');
        });
    }

    public function test_tc02_belum_ada_riwayat_menampilkan_pesan_kosong(): void
    {
        $this->meter(false);
        $this->browse(function (Browser $b) {
            $this->login($b, $this->budi)->clickLink('Riwayat Meter')->waitForText('Belum ada riwayat pencatatan meter.')
                ->assertSeeIn('@history-count', '0')->assertSeeIn('@history-usage', '0,00 m³');
            $this->assertDatabaseCount('meter_readings', 0);
            $this->evidence($b, 'PBI-3-TC02');
        });
    }
}
