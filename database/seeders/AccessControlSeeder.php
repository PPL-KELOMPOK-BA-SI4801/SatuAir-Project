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

        $defaults = [
            'bendahara' => ['dashboard.bendahara', 'access.manage', 'finance.manage', 'users.manage', 'meter.review'],
            'petugas' => ['field.manage', 'payments.view'],
            'masyarakat' => ['resident.use'],
        ];

        $newRoles = [];
        foreach ($roles as $name => $attributes) {
            $role = Role::firstOrCreate(['name' => $name], $attributes + ['is_system' => true]);
            if ($role->wasRecentlyCreated) {
                $newRoles[$name] = $role;
            }
        }

        $permissions = [
            ['name' => 'dashboard.bendahara', 'label' => 'Dashboard bendahara', 'group' => 'Bendahara'],
            ['name' => 'access.manage', 'label' => 'Mengatur hak akses', 'group' => 'Bendahara'],
            ['name' => 'finance.manage', 'label' => 'Mengelola keuangan', 'group' => 'Keuangan'],
            ['name' => 'payments.view', 'label' => 'Melihat pembayaran', 'group' => 'Keuangan'],
            ['name' => 'users.manage', 'label' => 'Mengelola pengguna', 'group' => 'Pengguna'],
            ['name' => 'field.manage', 'label' => 'Mengelola meter dan pengaduan', 'group' => 'Operasional'],
            ['name' => 'resident.use', 'label' => 'Menggunakan layanan masyarakat', 'group' => 'Layanan'],
            ['name' => 'meter.review', 'label' => 'Memeriksa pencatatan meter', 'group' => 'Operasional'],
        ];

        foreach ($permissions as $attributes) {
            Permission::firstOrCreate(['name' => $attributes['name']], $attributes);
        }

        foreach ($newRoles as $roleName => $role) {
            $role->permissions()->sync(
                Permission::whereIn('name', $defaults[$roleName] ?? [])->pluck('id')
            );
        }

        Role::where('name', 'admin')->delete();
    }
}
