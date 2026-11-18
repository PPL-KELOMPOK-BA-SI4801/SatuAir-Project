<?php

namespace Tests\Browser;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AccessPermissionTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_checkbox_grants_and_revokes_a_feature_and_saves_it_to_database(): void
    {
        $this->seed(AccessControlSeeder::class);

        $bendahara = User::factory()->create([
            'name' => 'Bendahara Uji',
            'email' => 'bendahara-akses@example.test',
            'role' => 'bendahara',
        ]);
        $petugas = User::factory()->create([
            'name' => 'Petugas Uji',
            'email' => 'petugas-akses@example.test',
            'role' => 'petugas',
        ]);
        $petugasRole = Role::where('name', 'petugas')->firstOrFail();
        $financePermission = Permission::where('name', 'finance.manage')->firstOrFail();

        $this->browse(function (Browser $browser) use ($bendahara, $petugas, $petugasRole, $financePermission): void {
            $browser->loginAs($bendahara)
                ->visit('/bendahara/hak-akses')
                ->within('#access-role-petugas', function (Browser $form): void {
                    $form->check('#permission-petugas-finance-manage')
                        ->press('Simpan Petugas');
                })
                ->assertSee('berhasil diperbarui');

            $this->assertDatabaseHas('permission_role', [
                'role_id' => $petugasRole->id,
                'permission_id' => $financePermission->id,
            ]);

            $browser->loginAs($petugas)
                ->visit('/petugas/dashboard')
                ->assertSeeLink('Rekap Keuangan')
                ->clickLink('Rekap Keuangan')
                ->assertPathIs('/bendahara/rekap')
                ->assertSee('Laporan Pendapatan Air Bersih');

            $browser->loginAs($bendahara)
                ->visit('/bendahara/hak-akses')
                ->within('#access-role-petugas', function (Browser $form): void {
                    $form->uncheck('#permission-petugas-finance-manage')
                        ->press('Simpan Petugas');
                });

            $this->assertDatabaseMissing('permission_role', [
                'role_id' => $petugasRole->id,
                'permission_id' => $financePermission->id,
            ]);

            $browser->loginAs($petugas)
                ->visit('/bendahara/rekap')
                ->assertSee('403');
        });
    }
}
