<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\Browser\Support\TeamDuskTestCase;

#[Group('PBI-7')]
class RegistrasiLoginTest extends TeamDuskTestCase
{
    private function registration(Browser $b, string $email = 'baru@example.test', string $password = 'password123'): void
    {
        $b->visit('/register')->deleteAllCookies()->visit('/register')
            ->type('name', 'Warga Dusk Baru')->type('email', $email)->type('phone', '081234567899')
            ->type('password', $password)->type('password_confirmation', $password)->check('terms');
    }

    public function test_tc01_registrasi_valid_membuat_akun_dan_dashboard(): void
    {
        $this->browse(function (Browser $b) {
            $this->registration($b);
            $b->press('Daftarkan Akun Sekarang')->waitForLocation('/masyarakat/dashboard')->assertAuthenticated();
            $user = User::where('email', 'baru@example.test')->firstOrFail();
            $this->assertSame('masyarakat', $user->role);
            $this->assertSame('active', $user->status);
            $this->assertNotEmpty($user->customer_id);
            $this->assertSame('081234567899', $user->phone);
            $this->assertTrue(Hash::check('password123', $user->password));
            $this->evidence($b, 'PBI-7-TC01');
        });
    }

    public function test_tc02_email_sudah_terdaftar_ditolak(): void
    {
        $this->browse(function (Browser $b) {
            $this->registration($b, $this->rina->email);
            $b->press('Daftarkan Akun Sekarang')->waitForText('Email sudah terdaftar.')->assertPathIs('/register');
            $this->assertSame(4, User::count());
            $this->evidence($b, 'PBI-7-TC02');
        });
    }

    public function test_tc03_password_tidak_memenuhi_ketentuan_ditolak(): void
    {
        $this->browse(function (Browser $b) {
            $this->registration($b, 'baru@example.test', 'abc');
            // Uji server juga, meski browser mungkin membatasi panjang password.
            $b->script('document.querySelector("form").noValidate = true');
            $b->press('Daftarkan Akun Sekarang')->waitForText('Password')->assertPathIs('/register');
            $this->assertDatabaseMissing('users', ['email' => 'baru@example.test']);
            $this->evidence($b, 'PBI-7-TC03');
        });
    }

    public function test_tc04_login_akun_aktif_berhasil(): void
    {
        $this->browse(function (Browser $b) {
            $this->login($b, $this->rina)->assertAuthenticatedAs($this->rina);
            $this->evidence($b, 'PBI-7-TC04');
        });
    }

    public function test_tc05_password_salah_ditolak(): void
    {
        $this->browse(function (Browser $b) {
            $b->visit('/login')->deleteAllCookies()->visit('/login')
                ->type('email', $this->rina->email)->type('password', 'passwordSalah')
                ->press('Masuk ke Akun')->waitForText('Email atau password yang Anda masukkan salah.')
                ->assertGuest()->assertPathIs('/login');
            $this->evidence($b, 'PBI-7-TC05');
        });
    }

    public function test_tc06_akun_tidak_aktif_ditolak(): void
    {
        $this->rina->update(['status' => 'inactive']);
        $this->browse(function (Browser $b) {
            $b->visit('/login')->deleteAllCookies()->visit('/login')
                ->type('email', $this->rina->email)->type('password', 'password123')
                ->press('Masuk ke Akun')->waitForText('Akun Anda tidak aktif. Silakan hubungi pengelola.')
                ->assertGuest()->assertPathIs('/login');
            $this->evidence($b, 'PBI-7-TC06');
        });
    }

    public function test_tc07_logout_mengakhiri_sesi(): void
    {
        $this->browse(function (Browser $b) {
            $this->login($b, $this->rina);
            $b->click('nav div.relative[x-data] > button')->waitFor('form[action$="/logout"] button')
                ->press('Keluar')->waitForLocation('/login')->assertGuest()
                ->visit('/masyarakat/dashboard')->assertPathIs('/login');
            $this->evidence($b, 'PBI-7-TC07');
        });
    }
}
