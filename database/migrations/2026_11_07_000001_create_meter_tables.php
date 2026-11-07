<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('nomor_meter')->unique();
            $table->string('wilayah');
            // Integer perseratus m³ menjaga perhitungan desimal tetap tepat di SQLite.
            $table->unsignedBigInteger('meter_awal_satuan');
            $table->date('tanggal_meter_awal');
            $table->timestamps();
        });
        Schema::create('pencatatan_meter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->restrictOnDelete();
            $table->char('periode', 7);
            $table->unsignedBigInteger('meter_awal_satuan');
            $table->unsignedBigInteger('meter_akhir_satuan');
            $table->unsignedBigInteger('pemakaian_satuan');
            $table->string('status')->default('menunggu_pemeriksaan_bendahara');
            $table->timestamps();
            $table->unique(['pelanggan_id', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pencatatan_meter');
        Schema::dropIfExists('pelanggan');
    }
};
