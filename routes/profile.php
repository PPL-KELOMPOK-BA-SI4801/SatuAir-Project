<?php

use Illuminate\Support\Facades\Route;

Route::get('/profile', fn () => 'Contoh halaman profil')
    ->middleware('auth')
    ->name('profile');