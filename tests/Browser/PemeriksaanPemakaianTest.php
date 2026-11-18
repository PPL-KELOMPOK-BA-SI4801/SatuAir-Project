<?php

namespace Tests\Browser;

use App\Models\{MeterReading, MeterReadingReview, WaterBill};
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\Browser\Support\TeamDuskTestCase;

#[Group('PBI-2')]
class PemeriksaanPemakaianTest extends TeamDuskTestCase
{
    private const IDENTITY = 'input[name="checks[identity_period]"]';
    private const USAGE = 'input[name="checks[meter_usage]"]';

    private function draft(Browser $b, MeterReading $reading, string $notes = 'Angka sesuai bukti meter.', bool $checked = true): MeterReadingReview
    {
        $b->visit('/bendahara/pemeriksaan/pencatatan/'.$reading->id)->clickLink('Buat Pemeriksaan');
        if ($checked) $b->check(self::IDENTITY)->check(self::USAGE);
        $b->type('notes', $notes)->press('Simpan Draf');
        $b->waitUntil('/^\\/bendahara\\/pemeriksaan\\/\\d+$/.test(location.pathname)');
        $review = MeterReadingReview::latest('id')->firstOrFail();
        $b->waitForLocation('/bendahara/pemeriksaan/'.$review->id)->assertSee('Draf pemeriksaan');
        return $review;
    }

    private function correct(Browser $b, MeterReading $reading, string $value): void
    {
        $b->visit('/petugas/readings/'.$reading->id.'/edit')->type('reading_value', $value)
            ->type('reason', 'Koreksi sesuai bukti dan catatan pemeriksaan.')
            ->press('Simpan Perubahan')->waitForLocation('/petugas/readings/'.$reading->id);
    }

    public function test_tc01_daftar_filter_dan_rincian_sesuai_pencatatan(): void
    {
        $reading = $this->pending();
        $this->browse(function (Browser $b) use ($reading) {
            $this->login($b, $this->bendahara)->visit('/bendahara/pemeriksaan')
                ->type('search', $reading->waterMeter->meter_number)->select('status', 'pending')->press('Filter')
                ->assertElementsCount('tbody tr', 1)->assertSeeIn('tbody', 'Budi Santoso')
                ->assertSeeIn('tbody', '25,00 m³')->assertSeeIn('tbody', 'Menunggu pemeriksaan')
                ->click('a[href$="/pencatatan/'.$reading->id.'"]')->assertSee('Rincian Pemeriksaan Pemakaian')
                ->assertSee('317,00')->assertSee('342,00')->assertSee('Ahmad Fauzi')
                ->assertSee('Angka hasil pencatatan Petugas.')->assertSeeIn('#total-pemakaian', '25,00 m³');
            $this->evidence($b, 'PBI-2-TC01');
        });
    }

    public function test_tc02_membuat_draf_menyimpan_checklist_catatan_dan_snapshot(): void
    {
        $reading = $this->pending();
        $this->browse(function (Browser $b) use ($reading) {
            $this->login($b, $this->bendahara);
            $review = $this->draft($b, $reading);
            $this->assertSame('draft', $review->status);
            $this->assertSame($this->bendahara->id, $review->reviewer_id);
            $this->assertSame($reading->id, $review->meter_reading_id);
            $this->assertSame(['identity_period' => true, 'meter_usage' => true], $review->checks);
            $this->assertSame('Angka sesuai bukti meter.', $review->notes);
            $this->assertSame('25.00', $review->snapshot['usage_m3']);
            $this->assertDatabaseCount('water_bills', 0);
            $b->assertSeeIn('section[aria-label="Hasil pemeriksaan"]', 'Angka sesuai bukti meter.');
            $this->evidence($b, 'PBI-2-TC02');
        });
    }

    public function test_tc03_edit_draf_tanpa_mengubah_angka_pencatatan(): void
    {
        $reading = $this->pending();
        $this->browse(function (Browser $b) use ($reading) {
            $this->login($b, $this->bendahara);
            $review = $this->draft($b, $reading);
            $b->clickLink('Edit Draf')->uncheck(self::USAGE)->type('notes', 'Pemeriksaan dilengkapi setelah melihat bukti.')
                ->press('Simpan Perubahan Draf')->waitForLocation('/bendahara/pemeriksaan/'.$review->id)
                ->assertSee('Pemeriksaan dilengkapi setelah melihat bukti.');
            $review->refresh();
            $this->assertFalse($review->checks['meter_usage']);
            $this->assertSame(2, $review->version);
            $this->assertSame('Pemeriksaan dilengkapi setelah melihat bukti.', $review->notes);
            $this->assertSame('342.00', $reading->fresh()->reading_value);
            $this->assertDatabaseCount('water_bills', 0);
            $this->evidence($b, 'PBI-2-TC03');
        });
    }

    public function test_tc04_batalkan_draf_soft_delete_dan_bisa_membuat_pengganti(): void
    {
        $reading = $this->pending();
        $this->browse(function (Browser $b) use ($reading) {
            $this->login($b, $this->bendahara);
            $review = $this->draft($b, $reading);
            $b->press('Batalkan Draf')->acceptDialog()->waitForLocation('/bendahara/pemeriksaan/pencatatan/'.$reading->id)
                ->assertSeeIn('section[aria-label="Riwayat pemeriksaan"]', 'Draf dibatalkan');
            $this->assertSoftDeleted('meter_reading_reviews', ['id' => $review->id]);
            $this->assertNull($reading->fresh()->deleted_at);
            $this->assertDatabaseCount('water_bills', 0);
            $new = $this->draft($b, $reading, 'Draf pengganti.');
            $this->assertNotSame($review->id, $new->id);
            $this->evidence($b, 'PBI-2-TC04');
        });
    }

    public function test_tc05_setujui_menerbitkan_satu_tagihan_dan_mengunci_koreksi(): void
    {
        $reading = $this->pending();
        $this->browse(function (Browser $b) use ($reading) {
            $this->login($b, $this->bendahara);
            $review = $this->draft($b, $reading);
            $b->press('Setujui & Terbitkan Tagihan')->waitForText('Disetujui')->assertSee('pemakaian 25,00 m³');
            $review->refresh();
            $this->assertSame('approved', $review->status);
            $this->assertNotNull($review->reviewed_at);
            $this->assertNotNull($reading->fresh()->verified_at);
            $this->assertDatabaseCount('water_bills', 1);
            $this->assertDatabaseHas('water_bills', ['meter_reading_id' => $reading->id, 'usage_m3' => '25.00', 'total_amount' => '127500.00']);
            $this->evidence($b, 'PBI-2-TC05-persetujuan');
            $this->login($b, $this->petugas)->visit('/petugas/readings/'.$reading->id.'/edit')
                ->assertDisabled('main button[disabled]')->assertSee('diverifikasi');
            $this->evidence($b, 'PBI-2-TC05-terkunci');
        });
    }

    public function test_tc06_pengembalian_koreksi_dan_pemeriksaan_baru_menjaga_riwayat(): void
    {
        $reading = $this->pending();
        $this->browse(function (Browser $b) use ($reading) {
            $this->login($b, $this->bendahara);
            $old = $this->draft($b, $reading, 'Angka foto berbeda, mohon koreksi.', false);
            $b->press('Kembalikan untuk Koreksi')->waitForText('Dikembalikan untuk koreksi');
            $this->login($b, $this->petugas)->visit('/petugas/readings/'.$reading->id)
                ->assertSee('Angka foto berbeda, mohon koreksi.');
            $this->correct($b, $reading, '347,00');
            $b->assertSeeIn('#total-pemakaian', '30,00 m³');
            $this->login($b, $this->bendahara);
            $new = $this->draft($b, $reading, 'Pemeriksaan setelah koreksi.');
            $this->assertSame('30.00', $new->snapshot['usage_m3']);
            $this->assertSame('returned', $old->fresh()->status);
            $this->assertSame('25.00', $old->fresh()->snapshot['usage_m3']);
            $this->assertDatabaseCount('water_bills', 0);
            $b->visit('/bendahara/pemeriksaan/'.$old->id)->assertSeeIn('[aria-label="Snapshot pemeriksaan"]', '25,00 m³');
            $this->evidence($b, 'PBI-2-TC06');
        });
    }

    public function test_tc07_checklist_tidak_lengkap_dan_alasan_kosong_ditolak(): void
    {
        $reading = $this->pending();
        $this->browse(function (Browser $b) use ($reading) {
            $this->login($b, $this->bendahara);
            $review = $this->draft($b, $reading, '', false);
            $b->press('Setujui & Terbitkan Tagihan')->waitForText('Kedua checklist wajib diperiksa sebelum persetujuan.');
            $this->evidence($b, 'PBI-2-TC07-checklist');
            $b->press('Kembalikan untuk Koreksi')->waitForText('Alasan pengembalian wajib diisi melalui Edit Draf.');
            $this->assertSame('draft', $review->fresh()->status);
            $this->assertNull($reading->fresh()->verified_at);
            $this->assertDatabaseCount('water_bills', 0);
            $this->evidence($b, 'PBI-2-TC07-alasan');
        });
    }

    public function test_tc08_data_dikoreksi_setelah_draf_keputusan_lama_ditolak(): void
    {
        $reading = $this->pending();
        $this->browse(function (Browser $b, Browser $p) use ($reading) {
            $this->login($b, $this->bendahara);
            $review = $this->draft($b, $reading);
            $this->login($p, $this->petugas);
            $this->correct($p, $reading, '347,00');
            $b->press('Setujui & Terbitkan Tagihan')->waitForText('Data berubah sejak halaman dibuka.')
                ->assertDisabled('button[value="approved"]')->clickLink('Edit Draf')
                ->assertNotChecked(self::IDENTITY)->assertNotChecked(self::USAGE)
                ->check(self::IDENTITY)->check(self::USAGE)->press('Simpan Perubahan Draf')
                ->waitForLocation('/bendahara/pemeriksaan/'.$review->id);
            $this->assertSame('draft', $review->fresh()->status);
            $this->assertSame('30.00', $review->fresh()->snapshot['usage_m3']);
            $this->assertNull($reading->fresh()->verified_at);
            $this->assertDatabaseCount('water_bills', 0);
            $b->assertSeeIn('[aria-label="Snapshot pemeriksaan"]', '30,00 m³');
            $this->evidence($b, 'PBI-2-TC08');
        });
    }

    public function test_tc09_dua_tab_tidak_menerbitkan_tagihan_ganda(): void
    {
        $reading = $this->pending();
        $this->browse(function (Browser $b) use ($reading) {
            $this->login($b, $this->bendahara);
            $review = $this->draft($b, $reading);
            $first = $b->driver->getWindowHandle();
            $b->script('window.open(location.href, "_blank")');
            $handles = $b->driver->getWindowHandles();
            $second = array_values(array_diff($handles, [$first]))[0];
            try {
                $b->driver->switchTo()->window($second);
                $b->waitFor('button[value="approved"]');
                $b->driver->switchTo()->window($first);
                $b->press('Setujui & Terbitkan Tagihan')->waitForText('Disetujui');
                $b->driver->switchTo()->window($second);
                $b->press('Setujui & Terbitkan Tagihan')->waitForText('Pemeriksaan sudah final atau draf telah dibatalkan.')
                    ->assertMissing('button[value="approved"]')->assertDontSee('Edit Draf')->assertDontSee('Batalkan Draf');
                $this->assertSame('approved', $review->fresh()->status);
                $this->assertSame(1, WaterBill::where('meter_reading_id', $reading->id)->count());
                $this->evidence($b, 'PBI-2-TC09');
            } finally {
                $b->driver->close();
                $b->driver->switchTo()->window($first);
            }
        });
    }
}
