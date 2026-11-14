<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller untuk mengelola profil masyarakat.
 *
 * Menyediakan fitur untuk melihat dan memperbarui
 * informasi profil pengguna.
 */
class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil pengguna.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $user = Auth::user();

        return view('masyarakat.profile.show', compact('user'));
    }

    /**
     * Perbarui informasi profil pengguna.
     *
     * Memvalidasi dan memperbarui nama, nomor telepon, dan alamat.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max'      => 'Nama lengkap maksimal 255 karakter.',
            'phone.max'     => 'Nomor telepon maksimal 20 karakter.',
            'address.max'   => 'Alamat maksimal 500 karakter.',
        ]);

        $user->update([
            'name'    => $request->name,
            'phone'   => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
