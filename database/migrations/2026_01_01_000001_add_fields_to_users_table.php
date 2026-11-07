<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['masyarakat', 'petugas', 'bendahara', 'admin'])->default('masyarakat')->after('phone');
            $table->text('address')->nullable()->after('role');
            $table->string('customer_id')->unique()->nullable()->after('address');
            $table->string('avatar')->nullable()->after('customer_id');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('avatar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['customer_id']);
            $table->dropColumn([
                'phone',
                'role',
                'address',
                'customer_id',
                'avatar',
                'status',
            ]);
        });
    }
};
