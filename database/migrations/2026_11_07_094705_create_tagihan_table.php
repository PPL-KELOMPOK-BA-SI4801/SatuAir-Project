
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pencatatan_meter_id')
                ->unique()
                ->constrained('pencatatan_meter')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('tarif_id')->nullable();

            $table->string('nomor_tagihan')->unique();
            $table->decimal('pemakaian_m3', 12, 2);
            $table->decimal('total', 15, 2);
            $table->date('jatuh_tempo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan');
    }
};