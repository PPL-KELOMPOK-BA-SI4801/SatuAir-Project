<?php

use Illuminate\Support\Facades\Route;

Route::get('/katalog', fn () => 'Contoh halaman katalog')
    ->name('katalog.index');