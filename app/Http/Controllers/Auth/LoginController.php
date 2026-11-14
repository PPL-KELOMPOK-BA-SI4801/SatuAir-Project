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
            'role' => ['required', 'in:masyarakat,petugas,bendahara,admin'],
        ]);

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withInput($request->only('email', 'role'))
                ->withErrors(['email' => 'Email atau password yang Anda masukkan salah.']);
        }

        $user = Auth::user();
        $selectedRole = $data['role'];
        $roleMatches = $user->role === $selectedRole
            || (in_array($user->role, ['bendahara', 'admin']) && in_array($selectedRole, ['bendahara', 'admin']));

        if (! $roleMatches) {
            Auth::logout();
            return back()->withInput($request->only('email', 'role'))
                ->withErrors(['role' => 'Anda tidak memiliki akses untuk peran ini.']);
        }

        $request->session()->regenerate();
        $path = match ($user->role) {
            'bendahara', 'admin' => '/bendahara/dashboard',
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
