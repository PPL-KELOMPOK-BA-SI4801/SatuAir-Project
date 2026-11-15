<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            DB::table('users')->where('role', 'admin')->update(['role' => 'bendahara']);
        }

        if (Schema::hasTable('roles')) {
            DB::table('roles')->where('name', 'admin')->delete();
        }
    }

    public function down(): void
    {
        // Keep migrated accounts as bendahara when rolling back this compatibility migration.
    }
};
