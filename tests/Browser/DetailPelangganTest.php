<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\Browser\Support\{DuskEnvironment, TeamDuskTestCase};

#[Group('PBI-10')]
class DetailPelangganTest extends TeamDuskTestCase
{
    private function detail(): string { return '/bendahara/pengguna/'.$this->rina->id; }

    private function openEdit(Browser $b): void
    {
        $this->login($b, $this->bendahara)->visit('/bendahara/pengguna');
        $b->click('a[href$="'.$this->detail().'"]')->assertSee('Detail Pelanggan')
            ->clickLink('Edit Data Pelanggan')->waitForText('Edit Data Pelanggan')->assertInputValue('name', 'Rina Marlina');
    }

    public function test_tc01_membuka_detail_pelanggan_sesuai_database(): void
    {
        $this->browse(function (Browser $b) {
            $this->login($b, $this->bendahara)->visit('/bendahara/pengguna')
                ->click('a[href$="'.$this->detail().'"]')->assertSee('Detail Pelanggan')
                ->assertInputValue('name', 'Rina Marlina')->assertInputValue('email', 'rina@gmail.com')
                ->assertInputValue('phone', $this->rina->phone)->assertInputValue('address', $this->rina->address)
                ->assertDisabled('#name');
            $this->evidence($b, 'PBI-10-TC01');
        });
    }

    public function test_tc02_perubahan_valid_tersimpan(): void
    {
        $this->browse(function (Browser $b) {
            $this->openEdit($b);
            $b->type('name', 'Rina Marlina Diperbarui')->type('email', 'rina.baru@example.test')
                ->type('phone', '081234567800')->type('address', 'Dusun II, Sukamaju')->press('Simpan Perubahan')
                ->waitForLocation($this->detail())->assertSee('Data pelanggan berhasil diperbarui.')
                ->assertInputValue('name', 'Rina Marlina Diperbarui');
            $this->assertDatabaseHas('users', ['id' => $this->rina->id, 'name' => 'Rina Marlina Diperbarui',
                'email' => 'rina.baru@example.test', 'phone' => '081234567800', 'address' => 'Dusun II, Sukamaju']);
            $this->evidence($b, 'PBI-10-TC02');
        });
    }

    public function test_tc03_kolom_wajib_kosong_ditolak(): void
    {
        $this->browse(function (Browser $b) {
            $this->openEdit($b);
            $b->type('name', '')->press('Simpan Perubahan')->assertScript('document.querySelector("#name").validity.valueMissing', true);
            $this->assertSame('Rina Marlina', $this->rina->fresh()->name);
            $this->evidence($b, 'PBI-10-TC03');
        });
    }

    public function test_tc04_format_email_tidak_valid_ditolak(): void
    {
        $this->browse(function (Browser $b) {
            $this->openEdit($b);
            $b->type('email', 'bukan-email')->press('Simpan Perubahan')->assertScript('document.querySelector("#email").validity.typeMismatch', true);
            $this->assertSame('rina@gmail.com', $this->rina->fresh()->email);
            $this->evidence($b, 'PBI-10-TC04');
        });
    }

    public function test_tc05_pelanggan_tidak_ditemukan(): void
    {
        $this->browse(function (Browser $b) {
            $this->login($b, $this->bendahara)->visit('/bendahara/pengguna/999999')->assertSee('404');
            $this->assertHttpStatus($b, 404);
            $this->evidence($b, 'PBI-10-TC05');
        });
    }

    public function test_tc06_gagal_query_simpan_tidak_mengubah_database(): void
    {
        $this->browse(function (Browser $b) {
            $this->openEdit($b);
            $b->type('name', 'Nama Yang Gagal Disimpan');
            DuskEnvironment::failNextQuery('customer-save');
            $b->press('Simpan Perubahan')->waitForText('Perubahan gagal disimpan. Silakan coba lagi.')
                ->assertPathIs($this->detail().'/profil/edit')->assertDontSee('Data pelanggan berhasil diperbarui.');
            $this->assertSame('Rina Marlina', $this->rina->fresh()->name);
            $this->assertFileDoesNotExist(DuskEnvironment::faultPath());
            $this->evidence($b, 'PBI-10-TC06');
        });
    }

    public function test_tc07_bukan_bendahara_tidak_bisa_detail_atau_edit(): void
    {
        $this->browse(function (Browser $b) {
            $this->login($b, $this->petugas)->visit($this->detail())->assertSee('403');
            $this->assertHttpStatus($b, 403);
            $b->visit($this->detail().'/profil/edit')->assertSee('403');
            $this->assertHttpStatus($b, 403);
            $this->evidence($b, 'PBI-10-TC07');
        });
    }

    public function test_tc08_detail_setelah_perubahan_tetap_menampilkan_data_baru(): void
    {
        $this->browse(function (Browser $b) {
            $this->openEdit($b);
            $b->type('address', 'Alamat terbaru setelah diperiksa')->press('Simpan Perubahan')->waitForLocation($this->detail())
                ->visit('/bendahara/pengguna')->click('a[href$="'.$this->detail().'"]')
                ->assertInputValue('address', 'Alamat terbaru setelah diperiksa');
            $this->assertDatabaseHas('users', ['id' => $this->rina->id, 'address' => 'Alamat terbaru setelah diperiksa']);
            $this->evidence($b, 'PBI-10-TC08');
        });
    }
}
