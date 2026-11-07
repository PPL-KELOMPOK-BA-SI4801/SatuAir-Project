<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller untuk autentikasi login pengguna.
 *
 * Menangani tampilan form login dengan tab pemilihan peran
 * (Masyarakat, Petugas, Admin), proses login, dan logout.
 */
class LoginController extends Controller
{
    /**
     * Tampilkan halaman login.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Proses autentikasi login pengguna.
     *
     * Memvalidasi kredensial email dan password, lalu mengarahkan
     * pengguna ke dashboard sesuai peran mereka.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
            'role'     => ['required', 'in:masyarakat,petugas,bendahara,admin'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'role.required'     => 'Peran wajib dipilih.',
            'role.in'           => 'Peran tidak valid.',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Verifikasi peran yang dipilih sesuai dengan peran pengguna (bendahara & admin kompatibel)
            $userRole = $user->role;
            $reqRole = $request->role;
            $roleMatches = ($userRole === $reqRole)
                || (in_array($userRole, ['bendahara', 'admin']) && in_array($reqRole, ['bendahara', 'admin']));

            if (!$roleMatches) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()
                    ->withInput($request->only('email', 'role'))
                    ->withErrors([
                        'role' => 'Anda tidak memiliki akses untuk peran ini.',
                    ]);
            }

            // Verifikasi status akun aktif
            if ($user->status !== 'active') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()
                    ->withInput($request->only('email', 'role'))
                    ->withErrors([
                        'email' => 'Akun Anda tidak aktif. Silakan hubungi administrator.',
                    ]);
            }

            // Arahkan ke dashboard sesuai peran
            return redirect()->intended($this->redirectPath($user->role));
        }

        return back()
            ->withInput($request->only('email', 'role'))
            ->withErrors([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ]);
    }

    /**
     * Proses logout pengguna.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda berhasil keluar dari sistem.');
    }

    /**
     * Tentukan path redirect berdasarkan peran pengguna.
     *
     * @param  string  $role
     * @return string
     */
    protected function redirectPath(string $role): string
    {
        return match ($role) {
            'bendahara', 'admin' => '/bendahara/dashboard',
            'petugas'            => '/petugas/dashboard',
            'masyarakat'         => '/masyarakat/dashboard',
            default              => '/',
        };
    }
}
