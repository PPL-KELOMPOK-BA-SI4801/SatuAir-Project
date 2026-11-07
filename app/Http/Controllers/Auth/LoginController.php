<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'role' => ['required', 'in:masyarakat,petugas,bendahara'],
        ]);

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withInput($request->only('email', 'role'))
                ->withErrors(['email' => 'Email atau password yang Anda masukkan salah.']);
        }

        $user = Auth::user();
        if ($user->role !== $data['role']) {
            Auth::logout();
            return back()->withInput($request->only('email', 'role'))
                ->withErrors(['role' => 'Peran yang dipilih tidak sesuai dengan akun.']);
        }

        $request->session()->regenerate();
        $path = match ($user->role) {
            'bendahara' => '/bendahara/dashboard',
            'petugas' => '/petugas/dashboard',
            'masyarakat' => '/masyarakat/dashboard',
            default => '/',
        };

        return redirect()->intended($path);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
