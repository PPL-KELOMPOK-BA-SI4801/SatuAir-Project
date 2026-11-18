<?php

use App\Http\Controllers\PencatatanMeterController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:petugas', 'permission:field.manage'])->group(function () {
    Route::get('/catat-meter', [PencatatanMeterController::class, 'index'])->name('meter.index');
    Route::get('/catat-meter/{pelanggan}/catat', [PencatatanMeterController::class, 'create'])->name('meter.create');
    Route::post('/catat-meter/{pelanggan}', [PencatatanMeterController::class, 'store'])->name('meter.store');
});
