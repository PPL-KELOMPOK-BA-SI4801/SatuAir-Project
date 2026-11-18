<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\Browser\Support\{DuskEnvironment, TeamDuskTestCase};

#[Group('PBI-9')]
class FilterPenggunaTest extends TeamDuskTestCase
{
    private function open(Browser $b): void
    {
        $this->login($b, $this->bendahara)->visit('/bendahara/pengguna')->waitForText('Manajemen Pengguna');
    }

    private function filter(Browser $b, string $role): void
    {
        $b->select('role', $role)->waitForReload(fn (Browser $page) => $page->press('Filter'))->waitForText('Manajemen Pengguna');
    }

    public function test_tc01_daftar_awal_menampilkan_seluruh_pengguna(): void
    {
        $this->browse(function (Browser $b) {
            $this->open($b);
            $b->assertElementsCount('tbody tr', 4)->assertSeeIn('tbody', 'Pak Hendra')
                ->assertSeeIn('tbody', 'Ahmad Fauzi')->assertSeeIn('tbody', 'Rina Marlina')->assertSeeIn('tbody', 'Budi Santoso');
            $this->evidence($b, 'PBI-9-TC01');
        });
    }

    public function test_tc02_filter_masyarakat(): void
    {
        $this->browse(function (Browser $b) {
            $this->open($b); $this->filter($b, 'masyarakat');
            $b->assertElementsCount('tbody tr', 2)->assertSeeIn('tbody', 'Rina Marlina')->assertSeeIn('tbody', 'Budi Santoso')
                ->assertDontSeeIn('tbody', 'Ahmad Fauzi')->assertDontSeeIn('tbody', 'Pak Hendra');
            $this->evidence($b, 'PBI-9-TC02');
        });
    }

    public function test_tc03_filter_petugas(): void
    {
        $this->browse(function (Browser $b) {
            $this->open($b); $this->filter($b, 'petugas');
            $b->assertElementsCount('tbody tr', 1)->assertSeeIn('tbody', 'Ahmad Fauzi')->assertDontSeeIn('tbody', 'Rina Marlina');
            $this->evidence($b, 'PBI-9-TC03');
        });
    }

    public function test_tc04_filter_admin_bendahara(): void
    {
        $this->browse(function (Browser $b) {
            $this->open($b); $this->filter($b, 'bendahara');
            $b->assertElementsCount('tbody tr', 1)->assertSeeIn('tbody', 'Pak Hendra')->assertDontSeeIn('tbody', 'Ahmad Fauzi');
            $this->evidence($b, 'PBI-9-TC04');
        });
    }

    public function test_tc05_semua_peran_mengembalikan_seluruh_pengguna(): void
    {
        $this->browse(function (Browser $b) {
            $this->open($b); $this->filter($b, 'masyarakat'); $this->filter($b, '');
            $b->assertElementsCount('tbody tr', 4)->assertSeeIn('tbody', 'Pak Hendra')->assertSeeIn('tbody', 'Ahmad Fauzi');
            $this->evidence($b, 'PBI-9-TC05');
        });
    }

    public function test_tc06_peran_tanpa_pengguna_menampilkan_pesan_kosong(): void
    {
        $this->petugas->delete();
        $this->browse(function (Browser $b) {
            $this->open($b); $this->filter($b, 'petugas');
            $b->assertSee('Tidak ditemukan pengguna dengan peran Petugas.')->assertMissing('tbody');
            $this->evidence($b, 'PBI-9-TC06');
        });
    }

    public function test_tc07_kegagalan_query_filter_menampilkan_pesan_gagal(): void
    {
        $this->browse(function (Browser $b) {
            $this->open($b);
            DuskEnvironment::failNextQuery('users-filter');
            $this->filter($b, 'masyarakat');
            $b->assertSee('Data pengguna gagal dimuat. Silakan coba lagi.');
            $this->assertHttpStatus($b, 503);
            $this->assertSame(4, User::count());
            $this->assertFileDoesNotExist(DuskEnvironment::faultPath());
            $this->evidence($b, 'PBI-9-TC07');
        });
    }

    public function test_tc08_pengguna_tanpa_hak_akses_ditolak(): void
    {
        $this->browse(function (Browser $b) {
            $this->login($b, $this->rina)->visit('/bendahara/pengguna')->assertSee('403');
            $this->assertHttpStatus($b, 403);
            $this->evidence($b, 'PBI-9-TC08');
        });
    }
}
