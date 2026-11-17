<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;

Route::middleware('auth')->prefix('pelanggan')->name('pelanggan.')->group(function (): void {
    Route::get('/', [CustomerController::class, 'index'])->name('index');
    Route::get('/{user}', [CustomerController::class, 'show'])->name('show');
    Route::get('/{user}/edit', [CustomerController::class, 'edit'])->name('edit');
    Route::put('/{user}', [CustomerController::class, 'update'])->name('update');
});
