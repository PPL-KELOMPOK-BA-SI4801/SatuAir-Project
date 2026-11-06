<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function (): void {
    Route::get('/dashboard', fn () => 'Contoh dashboard admin')->name('dashboard');
});