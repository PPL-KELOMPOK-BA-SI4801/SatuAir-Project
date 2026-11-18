<?php

namespace Tests\Browser;

use Facebook\WebDriver\WebDriverBy;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class MeterReadingTest extends DuskTestCase
{
    private string $email = 'yuka@gmail.com';
    private string $password = '12345678';

    private string $fotoPath;
    private int $delay = 1500; 

    protected function setUp(): void
    {
        parent::setUp();

        $this->fotoPath = sys_get_temp_dir() . '/foto_meter_test.png';
        file_put_contents($this->fotoPath, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));
    }

private function loginPetugas(Browser $browser): Browser
{
    return $browser->visit('/')
        ->deleteCookie(config('session.cookie'))   
        ->visit('/login')
        ->pause($this->delay)
        ->assertSee('Masuk ke Akun')
        ->clickAtXPath("//*[normalize-space()='Petugas']")  
        ->pause($this->delay)
        ->typeSlowly('email', $this->email)
        ->pause(500)
        ->typeSlowly('password', $this->password)
        ->pause($this->delay)
        ->press('Masuk ke Akun')
        ->assertPathIs('/petugas/dashboard')
        ->pause($this->delay);
}

    private function klikCatatPelangganBelumDicatat(Browser $browser): void
    {
        $browser->driver->findElement(
            WebDriverBy::xpath("//*[self::a or self::button][normalize-space()='Catat']")
        )->click();
    }

    public function test_TC_07_01_tampil_jadwal_pencatatan_meter(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginPetugas($browser);

            $browser->assertSee('Halo,')
                ->clickLink('Catat Meter')
                ->assertPathIs('/petugas/meters')
                ->assertSee('Jadwal Pencatatan Meter')
                ->assertSee('PELANGGAN')
                ->assertSee('NO. METER')
                ->assertSee('WILAYAH')
                ->assertSee('STATUS');
        });
    }

    public function test_TC_07_02_cari_pelanggan_berdasarkan_nama(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginPetugas($browser);

            $browser->clickLink('Catat Meter')
                ->type('input[placeholder="Nama atau nomor meter"]', 'turner')
                ->press('Filter')
                ->assertSee('turner')
                ->assertDontSee('putra');
        });
    }

    public function test_TC_07_05_cari_pelanggan_tidak_ditemukan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginPetugas($browser);

            $browser->clickLink('Catat Meter')
                ->type('input[placeholder="Nama atau nomor meter"]', 'zzzzz')
                ->press('Filter')
                ->assertDontSee('turner');
        });
    }
public function test_TC_08_01_catat_meter_valid(): void
{
    $this->browse(function (Browser $browser) {
        $this->loginPetugas($browser);

        $browser->clickLink('Catat Meter')
            ->assertPathIs('/petugas/meters')
            ->pause($this->delay)
            ->assertSee('BELUM DICATAT');

        $this->klikCatatPelangganBelumDicatat($browser);

        $browser->pause($this->delay)
            ->assertSee('Catat Meter Pelanggan')
            ->typeSlowly('reading_value', '25')
            ->pause($this->delay)
            ->attach('input[type=file]', $this->fotoPath)
            ->pause($this->delay)
            ->screenshot('TC-08-01-sebelum-simpan')
            ->script('window.scrollTo(0, document.body.scrollHeight);');

        $browser->pause(1000)
            ->press('Simpan pencatatan')
            ->pause($this->delay)
            ->assertPathIs('/petugas/meters')
            ->assertSee('SUDAH DICATAT')
            ->screenshot('TC-08-01-berhasil')
            ->pause(2000);
    });
}
    public function test_batal_kembali_ke_jadwal(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginPetugas($browser);

            $browser->clickLink('Catat Meter');
            $this->klikCatatPelangganBelumDicatat($browser);

            $browser->type('reading_value', '10')
                ->clickLink('Batal')
                ->assertPathIs('/petugas/meters');
        });
    }
}