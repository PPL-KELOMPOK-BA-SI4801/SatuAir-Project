
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RiwayatMeterController;

require __DIR__.'/eco-gui.php';
require __DIR__.'/profile.php';
require __DIR__.'/eco-katalog.php';
require __DIR__.'/eco-klaim.php';
require __DIR__.'/auth.php';
require __DIR__.'/eco-admin.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/riwayat-meter', [RiwayatMeterController::class, 'index'])
        ->name('riwayat-meter.index');

    Route::get('/riwayat-meter/{id}', [RiwayatMeterController::class, 'show'])
        ->name('riwayat-meter.show');
});