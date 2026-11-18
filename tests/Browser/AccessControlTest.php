<?php

namespace Tests\Browser;

use App\Models\{Permission, Role};
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\Browser\Support\TeamDuskTestCase;

/** Melengkapi tes Saptya yang sudah ada, mengikuti TC-01 sampai TC-08 Jira PBI-8. */
#[Group('PBI-8')]
class AccessControlTest extends TeamDuskTestCase
{
    private function role(string $name): Role { return Role::where('name', $name)->firstOrFail(); }
    private function permission(string $name): int { return Permission::where('name', $name)->value('id'); }
    private function form(string $name): string { return 'form[action$="/hak-akses/'.$this->role($name)->id.'"]'; }

    public function test_tc01_bendahara_dengan_permission_membuka_hak_akses(): void
    {
        $this->browse(function (Browser $b) {
            $this->login($b, $this->bendahara)->visit('/bendahara/hak-akses')->assertSee('Pengaturan Hak Akses');
            $this->evidence($b, 'PBI-8-TC01');
        });
    }

    public function test_tc02_bendahara_tanpa_permission_ditolak_403(): void
    {
        $this->browse(function (Browser $b) {
            $this->login($b, $this->bendahara);
            $this->role('bendahara')->permissions()->detach($this->permission('access.manage'));
            $b->visit('/bendahara/hak-akses')->assertSee('403');
            $this->assertHttpStatus($b, 403);
            $this->evidence($b, 'PBI-8-TC02');
        });
    }

    public function test_tc03_permission_valid_tersimpan_dan_akses_berubah(): void
    {
        $role = $this->role('petugas');
        $permission = $this->permission('field.manage');
        $role->permissions()->detach($permission);
        $this->browse(function (Browser $b) use ($role, $permission) {
            $this->login($b, $this->bendahara)->visit('/bendahara/hak-akses')
                ->check($this->form('petugas').' input[type="checkbox"][value="'.$permission.'"]')
                ->press('Simpan Petugas')->waitForText('Hak akses untuk Petugas berhasil diperbarui.');
            $this->assertDatabaseHas('permission_role', ['role_id' => $role->id, 'permission_id' => $permission]);
            $this->login($b, $this->petugas)->visit('/petugas/meters')->assertSee('Pencatatan Water Meter');
            $this->evidence($b, 'PBI-8-TC03');
        });
    }

    public function test_tc04_permission_id_tidak_tersedia_ditolak_tanpa_perubahan(): void
    {
        $role = $this->role('petugas');
        $before = $role->permissions()->pluck('permissions.id')->all();
        $this->browse(function (Browser $b) use ($role, $before) {
            $this->login($b, $this->bendahara)->visit('/bendahara/hak-akses');
            // Simulasikan request termanipulasi, tetap dikirim oleh form browser dengan CSRF asli.
            $selector = json_encode($this->form('petugas'));
            $b->script("const f=document.querySelector($selector); const i=document.createElement('input'); i.type='hidden'; i.name='permissions[]'; i.value='999999'; f.appendChild(i);");
            $b->press('Simpan Petugas')->waitForText('Periksa kembali isian.');
            $this->assertEqualsCanonicalizing($before, $role->permissions()->pluck('permissions.id')->all());
            $this->assertDatabaseMissing('permission_role', ['role_id' => $role->id, 'permission_id' => 999999]);
            $this->evidence($b, 'PBI-8-TC04');
        });
    }

    public function test_tc05_masyarakat_tanpa_permission_ditolak(): void
    {
        $this->browse(function (Browser $b) {
            $this->login($b, $this->rina);
            $this->role('masyarakat')->permissions()->detach($this->permission('resident.use'));
            $b->visit('/masyarakat/meter-history')->assertSee('403');
            $this->assertHttpStatus($b, 403);
            $this->evidence($b, 'PBI-8-TC05');
        });
    }

    public function test_tc06_petugas_tanpa_permission_ditolak(): void
    {
        $this->browse(function (Browser $b) {
            $this->login($b, $this->petugas);
            $this->role('petugas')->permissions()->detach($this->permission('field.manage'));
            $b->visit('/petugas/meters')->assertSee('403');
            $this->assertHttpStatus($b, 403);
            $this->evidence($b, 'PBI-8-TC06');
        });
    }

    public function test_tc07_url_langsung_tetap_memeriksa_izin(): void
    {
        $this->browse(function (Browser $b) {
            $this->login($b, $this->petugas)->visit('/bendahara/hak-akses')->assertSee('403');
            $this->assertHttpStatus($b, 403);
            $b->visit('/bendahara/pengguna')->assertSee('403');
            $this->assertHttpStatus($b, 403);
            $this->evidence($b, 'PBI-8-TC07');
        });
    }

    public function test_tc08_bendahara_tidak_bisa_mencabut_access_manage_sendiri(): void
    {
        $role = $this->role('bendahara');
        $permission = $this->permission('access.manage');
        $this->browse(function (Browser $b) use ($role, $permission) {
            $this->login($b, $this->bendahara)->visit('/bendahara/hak-akses')
                ->assertDisabled($this->form('bendahara').' input[type="checkbox"][value="'.$permission.'"]');
            // Hilangkan juga hidden input untuk membuktikan server tidak mempercayai request.
            $selector = json_encode($this->form('bendahara').' input[name="permissions[]"][value="'.$permission.'"]');
            $b->script("document.querySelectorAll($selector).forEach(e=>e.remove())");
            $b->press('Simpan Bendahara')->waitForText('Hak akses untuk Bendahara berhasil diperbarui.');
            $this->assertDatabaseHas('permission_role', ['role_id' => $role->id, 'permission_id' => $permission]);
            $b->visit('/bendahara/hak-akses')->assertSee('Pengaturan Hak Akses');
            $this->evidence($b, 'PBI-8-TC08');
        });
    }
}
