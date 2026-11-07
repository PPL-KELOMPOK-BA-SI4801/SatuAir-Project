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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('ticket_number')->unique();
            $table->foreignId('category_id')->constrained('complaint_categories')->onDelete('cascade');
            $table->foreignId('area_id')->nullable()->constrained()->onDelete('set null');
            $table->text('location');
            $table->text('description');
            $table->string('photo')->nullable();
            $table->datetime('occurred_at')->nullable();
            $table->enum('status', ['pending', 'verified', 'in_progress', 'resolved', 'rejected'])->default('pending');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->text('rejection_reason')->nullable();
            $table->datetime('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->string('resolution_photo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
