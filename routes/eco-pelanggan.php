<?php

use Illuminate\Support\Facades\Route;

Route::prefix('pelanggan')->name('pelanggan.')->group(function (): void {
    Route::get('/{id}', fn (string $id) => view('lihat-edit-pelanggan', [
        'id' => $id,
        'editable' => false,
    ]))->name('show');

    Route::get('/{id}/edit', fn (string $id) => view('lihat-edit-pelanggan', [
        'id' => $id,
        'editable' => true,
    ]))->name('edit');
});
