<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AccessControlSeeder extends Seeder
{
    public function run(): void
    {
        $legacyDashboardPermission = Permission::where('name', 'dashboard.admin')->first();
        if ($legacyDashboardPermission && ! Permission::where('name', 'dashboard.bendahara')->exists()) {
            $legacyDashboardPermission->update([
                'name' => 'dashboard.bendahara',
                'label' => 'Dashboard bendahara',
                'group' => 'Bendahara',
            ]);
        } elseif ($legacyDashboardPermission) {
            $legacyDashboardPermission->delete();
        }

        $roles = [
            'bendahara' => ['label' => 'Bendahara', 'description' => 'Pengelola keuangan dan data operasional.'],
            'petugas' => ['label' => 'Petugas', 'description' => 'Petugas operasional lapangan.'],
            'masyarakat' => ['label' => 'Masyarakat', 'description' => 'Pengguna layanan air.'],
        ];

        $permissions = [
            ['name' => 'dashboard.bendahara', 'label' => 'Dashboard bendahara', 'group' => 'Bendahara'],
            ['name' => 'access.manage', 'label' => 'Mengatur hak akses', 'group' => 'Bendahara'],
            ['name' => 'finance.manage', 'label' => 'Mengelola keuangan', 'group' => 'Keuangan'],
            ['name' => 'users.manage', 'label' => 'Mengelola pengguna', 'group' => 'Pengguna'],
            ['name' => 'field.manage', 'label' => 'Mengelola meter dan pengaduan', 'group' => 'Operasional'],
            ['name' => 'resident.use', 'label' => 'Menggunakan layanan masyarakat', 'group' => 'Layanan'],
        ];

        foreach ($roles as $name => $attributes) {
            Role::firstOrCreate(['name' => $name], $attributes + ['is_system' => true]);
        }

        foreach ($permissions as $attributes) {
            Permission::firstOrCreate(['name' => $attributes['name']], $attributes);
        }

        $defaults = [
            'bendahara' => ['dashboard.bendahara', 'access.manage', 'finance.manage', 'users.manage'],
            'petugas' => ['field.manage'],
            'masyarakat' => ['resident.use'],
        ];

        foreach ($defaults as $roleName => $permissionNames) {
            Role::where('name', $roleName)->firstOrFail()->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', $permissionNames)->pluck('id')
            );
        }

        Role::where('name', 'admin')->delete();
    }
}
