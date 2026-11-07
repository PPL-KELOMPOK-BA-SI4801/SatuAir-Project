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
        Schema::create('tariff_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // A2, A1, A, B, C, S
            $table->string('name'); // Perusahaan, Rumah Tangga Mapan, etc
            $table->string('description')->nullable();
            $table->decimal('rate_per_m3', 10, 2);
            $table->decimal('admin_fee', 10, 2)->default(2500);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tariff_categories');
    }
};
