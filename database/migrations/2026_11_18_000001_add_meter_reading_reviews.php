<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->boolean('requires_review')->default(true)->index();
        });
        // Catatan/tagihan sebelum penerapan PBI-10 tidak diubah menjadi antrean baru.
        DB::table('meter_readings')->update(['requires_review' => false]);

        Schema::create('meter_reading_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meter_reading_id')->constrained()->restrictOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('draft')->index();
            $table->json('checks');
            $table->text('notes')->nullable();
            $table->json('snapshot');
            $table->string('reading_revision', 64);
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('reviewed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('water_bills', function (Blueprint $table) {
            $table->foreignId('meter_reading_id')->nullable()->unique()->constrained()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('water_bills', function (Blueprint $table) {
            $table->dropConstrainedForeignId('meter_reading_id');
        });
        Schema::dropIfExists('meter_reading_reviews');
        Schema::table('meter_readings', fn (Blueprint $table) => $table->dropColumn('requires_review'));
    }
};
