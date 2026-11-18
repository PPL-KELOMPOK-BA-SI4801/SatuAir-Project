<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $reviewPermission = Permission::firstOrCreate(
            ['name' => 'meter.review'],
            ['label' => 'Memeriksa pencatatan meter', 'group' => 'Operasional']
        );
        $paymentPermission = Permission::firstOrCreate(
            ['name' => 'payments.view'],
            ['label' => 'Melihat pembayaran', 'group' => 'Keuangan']
        );

        Role::where('name', 'bendahara')->first()?->permissions()->syncWithoutDetaching([$reviewPermission->id]);
        Role::where('name', 'petugas')->first()?->permissions()->syncWithoutDetaching([$paymentPermission->id]);
    }

    public function down(): void
    {
        Permission::whereIn('name', ['meter.review', 'payments.view'])->delete();
    }
};
