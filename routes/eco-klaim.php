<?php

use Illuminate\Support\Facades\Route;

Route::get('/klaim', fn () => 'Contoh halaman klaim')
    ->name('klaim.index');