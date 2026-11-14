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
        Schema::create('yearly_snapshots', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->unique();
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->decimal('total_bills_generated', 15, 2)->default(0);
            $table->integer('total_customers')->default(0);
            $table->integer('total_bills_count')->default(0);
            $table->integer('paid_bills_count')->default(0);
            $table->integer('unpaid_bills_count')->default(0);
            $table->decimal('total_arrears', 15, 2)->default(0); // tunggakan
            $table->json('monthly_breakdown')->nullable(); // JSON array of 12 months
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yearly_snapshots');
    }
};
