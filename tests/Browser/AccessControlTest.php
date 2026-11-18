<?php

namespace Tests\Browser;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-02 – Dusk Browser Test: Pengaturan Hak Akses
 *
 * Skenario yang diuji:
 * 1. Bendahara membuka halaman Hak Akses → berhasil.
 * 2. Bendahara mengubah checkbox → tersimpan di database.
 * 3. Petugas yang mendapat izin bisa membuka fitur.
 * 4. Setelah izin dicabut, akses petugas ditolak (403).
 * 5. Masyarakat yang mendapat izin resident.use bisa membuka dashboard.
 * 6. Bendahara tidak bisa menghapus izin access.manage miliknya sendiri.
 */
class AccessControlTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccessControlSeeder::class);
    }

    // ─────────────────────────────────────────────────────────────
    // 1. Bendahara berhasil membuka halaman Hak Akses
    // ─────────────────────────────────────────────────────────────
    public function test_bendahara_dapat_membuka_halaman_hak_akses(): void
    {
        $bendahara = User::factory()->create(['role' => 'bendahara']);

        $this->browse(function (Browser $browser) use ($bendahara) {
            $browser->loginAs($bendahara)
                    ->visit(route('bendahara.access.index'))
                    ->assertSee('Pengaturan Hak Akses')
                    ->assertSee('Simpan');
        });
    }

    // ─────────────────────────────────────────────────────────────
    // 2. Petugas tanpa izin access.manage diblokir (403)
    // ─────────────────────────────────────────────────────────────
    public function test_petugas_tidak_bisa_membuka_halaman_hak_akses(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);

        $this->browse(function (Browser $browser) use ($petugas) {
            $browser->loginAs($petugas)
                    ->visit(route('bendahara.access.index'))
                    ->assertSee('403');
        });
    }

    // ─────────────────────────────────────────────────────────────
    // 3. Bendahara mengubah checkbox → tersimpan di database
    // ─────────────────────────────────────────────────────────────
    public function test_bendahara_menyimpan_perubahan_permission_ke_database(): void
    {
        $bendahara = User::factory()->create(['role' => 'bendahara']);
        $roleP     = Role::where('name', 'petugas')->firstOrFail();
        $permP     = Permission::where('name', 'finance.manage')->firstOrFail();

        // Pastikan petugas belum punya finance.manage
        $roleP->permissions()->detach($permP->id);

        $this->browse(function (Browser $browser) use ($bendahara, $roleP, $permP) {
            $browser->loginAs($bendahara)
                    ->visit(route('bendahara.access.index'))
                    // Centang izin finance.manage pada form peran Petugas
                    ->check("permissions[]", $permP->id)
                    ->press("Simpan {$roleP->label}")
                    ->waitForText('berhasil diperbarui');
        });

        // Verifikasi database
        $this->assertDatabaseHas('permission_role', [
            'role_id'       => $roleP->id,
            'permission_id' => $permP->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // 4. Petugas yang mendapat izin field.manage bisa akses fitur meter
    // ─────────────────────────────────────────────────────────────
    public function test_petugas_dengan_izin_field_manage_bisa_akses_meter(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);
        // Pastikan petugas punya field.manage (default seeder sudah assign)
        $roleP   = Role::where('name', 'petugas')->firstOrFail();
        $permF   = Permission::where('name', 'field.manage')->firstOrFail();
        $roleP->permissions()->syncWithoutDetaching([$permF->id]);

        $this->browse(function (Browser $browser) use ($petugas) {
            $browser->loginAs($petugas)
                    ->visit(route('petugas.dashboard'))
                    ->assertDontSee('403')
                    ->assertDontSee('Akses ditolak');
        });
    }

    // ─────────────────────────────────────────────────────────────
    // 5. Petugas yang dicabut izin field.manage diblokir
    // ─────────────────────────────────────────────────────────────
    public function test_petugas_tanpa_izin_field_manage_diblokir_dari_meter(): void
    {
        $bendahara = User::factory()->create(['role' => 'bendahara']);
        $petugas   = User::factory()->create(['role' => 'petugas']);

        // Cabut SEMUA permission dari petugas
        $roleP = Role::where('name', 'petugas')->firstOrFail();
        $roleP->permissions()->sync([]);

        $this->browse(function (Browser $browser) use ($petugas) {
            $browser->loginAs($petugas)
                    ->visit(route('petugas.dashboard'))
                    ->assertSee('403');
        });
    }

    // ─────────────────────────────────────────────────────────────
    // 6. Masyarakat dengan resident.use bisa membuka dashboard
    // ─────────────────────────────────────────────────────────────
    public function test_masyarakat_dengan_izin_resident_use_bisa_membuka_dashboard(): void
    {
        $masyarakat = User::factory()->create(['role' => 'masyarakat']);
        $roleM      = Role::where('name', 'masyarakat')->firstOrFail();
        $permR      = Permission::where('name', 'resident.use')->firstOrFail();
        $roleM->permissions()->syncWithoutDetaching([$permR->id]);

        $this->browse(function (Browser $browser) use ($masyarakat) {
            $browser->loginAs($masyarakat)
                    ->visit(route('masyarakat.dashboard'))
                    ->assertDontSee('403');
        });
    }

    // ─────────────────────────────────────────────────────────────
    // 7. Bendahara tidak bisa menghapus izin access.manage dirinya sendiri
    // ─────────────────────────────────────────────────────────────
    public function test_bendahara_tidak_bisa_cabut_izin_access_manage_sendiri(): void
    {
        $bendahara = User::factory()->create(['role' => 'bendahara']);
        $roleB     = Role::where('name', 'bendahara')->firstOrFail();

        $this->browse(function (Browser $browser) use ($bendahara, $roleB) {
            $browser->loginAs($bendahara)
                    ->visit(route('bendahara.access.index'));

            // Hapus centang semua lalu simpan
            $browser->script("
                document.querySelectorAll('form').forEach(function(form) {
                    if (form.action.includes('/{$roleB->id}')) {
                        form.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
                        form.querySelector('button[type=submit]').click();
                    }
                });
            ");

            $browser->waitForText('berhasil diperbarui');
        });

        // Bendahara tetap punya access.manage di database
        $this->assertTrue($bendahara->fresh()->hasPermission('access.manage'));
    }
}
