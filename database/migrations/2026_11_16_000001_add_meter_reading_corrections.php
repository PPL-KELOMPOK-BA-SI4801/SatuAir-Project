<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->softDeletes();
            // PBI-10 dapat mengisi penanda ini; PBI-09 tidak melakukan verifikasi.
            $table->timestamp('verified_at')->nullable();
        });

        Schema::table('water_bills', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::create('meter_reading_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meter_reading_id')->constrained()->restrictOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 20);
            $table->text('reason');
            $table->json('before');
            $table->json('after');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_reading_changes');
        Schema::table('water_bills', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('verified_at');
        });
    }
};
