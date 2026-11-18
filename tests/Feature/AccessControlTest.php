<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccessControlSeeder::class);
    }

    public function test_authorized_role_can_open_access_settings(): void
    {
        $user = User::factory()->create(['role' => 'bendahara']);

        $this->actingAs($user)->get(route('bendahara.access.index'))->assertOk()->assertSee('Pengaturan Hak Akses');
    }

    public function test_role_without_access_permission_is_forbidden(): void
    {
        $user = User::factory()->create(['role' => 'petugas']);

        $this->actingAs($user)->get(route('bendahara.access.index'))->assertForbidden();
    }

    public function test_role_permissions_can_be_updated(): void
    {
        $user = User::factory()->create(['role' => 'bendahara']);
        $role = Role::where('name', 'petugas')->firstOrFail();
        $permission = $role->permissions()->firstOrFail();

        $this->actingAs($user)->put(route('bendahara.access.update', $role), [
            'label' => $role->label,
            'description' => $role->description,
            'permissions' => [$permission->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('permission_role', [
            'role_id' => $role->id,
            'permission_id' => $permission->id,
        ]);
    }

    public function test_saving_and_removing_finance_permission_changes_petugas_access(): void
    {
        $bendahara = User::factory()->create(['role' => 'bendahara']);
        $petugas = User::factory()->create(['role' => 'petugas']);
        $role = Role::where('name', 'petugas')->firstOrFail();
        $finance = Permission::where('name', 'finance.manage')->firstOrFail();
        $field = Permission::where('name', 'field.manage')->firstOrFail();

        $this->actingAs($petugas)->get(route('bendahara.rekap.index'))->assertForbidden();

        $this->actingAs($bendahara)->put(route('bendahara.access.update', $role), [
            'label' => $role->label,
            'description' => $role->description,
            'permissions' => [$field->id, $finance->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('permission_role', [
            'role_id' => $role->id,
            'permission_id' => $finance->id,
        ]);
        $this->actingAs($petugas)->get(route('bendahara.rekap.index'))->assertOk();

        $this->actingAs($bendahara)->put(route('bendahara.access.update', $role), [
            'label' => $role->label,
            'description' => $role->description,
            'permissions' => [$field->id],
        ])->assertRedirect();

        $this->assertDatabaseMissing('permission_role', [
            'role_id' => $role->id,
            'permission_id' => $finance->id,
        ]);
        $this->seed(AccessControlSeeder::class);
        $this->assertDatabaseMissing('permission_role', [
            'role_id' => $role->id,
            'permission_id' => $finance->id,
        ]);
        $this->actingAs($petugas)->get(route('bendahara.rekap.index'))->assertForbidden();
    }

    public function test_access_management_stays_exclusive_to_bendahara(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $role = Role::where('name', 'petugas')->firstOrFail();
        $permission = Permission::where('name', 'access.manage')->firstOrFail();
        $role->permissions()->attach($permission);

        $this->actingAs($petugas)->get(route('bendahara.access.index'))->assertForbidden();
    }

    public function test_user_management_permission_does_not_grant_bendahara_own_profile(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $role = Role::where('name', 'petugas')->firstOrFail();
        $permission = \App\Models\Permission::where('name', 'users.manage')->firstOrFail();
        $role->permissions()->attach($permission);

        $this->actingAs($petugas)->get(route('bendahara.profile.edit'))->assertForbidden();
    }

    public function test_bendahara_cannot_remove_its_own_access_management_permission(): void
    {
        $user = User::factory()->create(['role' => 'bendahara']);
        $role = Role::where('name', 'bendahara')->firstOrFail();

        $this->actingAs($user)->put(route('bendahara.access.update', $role), [
            'label' => $role->label,
            'description' => $role->description,
            'permissions' => [],
        ])->assertRedirect();

        $this->assertTrue($user->fresh()->hasPermission('access.manage'));
    }

    public function test_public_home_login_and_register_pages_remain_available(): void
    {
        $this->get('/')->assertOk();
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();
        $this->get('/admin/dashboard')->assertNotFound();
    }

    public function test_registered_resident_can_open_dashboard_with_seeded_access(): void
    {
        $this->post(route('register'), [
            'name' => 'Warga Baru',
            'email' => 'warga-baru@example.test',
            'phone' => '081234567890',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertRedirect('/masyarakat/dashboard');

        $this->assertAuthenticated();
        $this->get('/masyarakat/dashboard')->assertOk();
    }
}
