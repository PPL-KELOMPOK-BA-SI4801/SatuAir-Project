<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Controller untuk registrasi pengguna baru.
 *
 * Hanya menangani registrasi untuk peran masyarakat.
 * Membuat akun baru dengan customer_id otomatis.
 */
class RegisterController extends Controller
{
    /**
     * Tampilkan halaman formulir registrasi.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Proses registrasi pengguna baru.
     *
     * Memvalidasi input, membuat user dengan peran masyarakat,
     * meng-generate customer_id otomatis, dan mengarahkan ke dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'name.max'           => 'Nama lengkap maksimal 255 karakter.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email sudah terdaftar.',
            'phone.max'          => 'Nomor telepon maksimal 20 karakter.',
            'password.required'  => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'       => 'Password minimal 8 karakter.',
        ]);

        // Generate customer_id otomatis
        $customerId = 'CUST-' . str_pad(User::count() + 1, 5, '0', STR_PAD_LEFT);

        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'password'    => Hash::make($request->password),
            'role'        => 'masyarakat',
            'status'      => 'active',
            'customer_id' => $customerId,
        ]);

        Auth::login($user);

        return redirect('/masyarakat/dashboard')
            ->with('success', 'Registrasi berhasil! Selamat datang di SatuAir.');
    }
}
