<?php

use Illuminate\Support\Facades\Route;

// Placeholder route. Hubungkan ke controller autentikasi saat fitur dibuat.
Route::get('/login', fn () => 'Form login belum dibuat')->name('login');
Route::post('/logout', fn () => redirect()->route('home'))->middleware('auth')->name('logout');