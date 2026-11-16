@extends('layouts.guest')

@section('title', 'Masuk ke Akun — SatuAir')

@section('content')
<div class="min-h-screen w-full flex flex-col lg:flex-row bg-[#030712]"
     x-data="{
        activeTab: '{{ old('role', 'masyarakat') }}',
        showPassword: false
     }">

    {{-- ==================== LEFT COLUMN: CLEAN SIMPLIFIED LOGIN FORM ==================== --}}
    <div class="w-full lg:w-1/2 flex flex-col justify-between p-6 sm:p-10 lg:p-12 xl:p-14 bg-white min-h-screen z-10">

        {{-- Top Bar: Logo & Back Link --}}
        <div class="flex items-center justify-between">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center shadow-md shadow-sky-500/20 text-white transition-transform group-hover:scale-105">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z" />
                    </svg>
                </div>
                <span class="font-bold text-2xl tracking-wider text-slate-900">
                    SATU<span class="text-sky-500 font-light">AIR</span>
                </span>
            </a>

            <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-slate-500 hover:text-slate-900 transition-colors py-1.5 px-3 rounded-full hover:bg-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Beranda</span>
            </a>
        </div>

        {{-- Center: Form Wrapper --}}
        <div class="w-full max-w-sm mx-auto my-auto py-8">
            {{-- Header --}}
            <div class="mb-7">
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Masuk ke Akun</h1>
                <p class="text-sm text-slate-500 mt-1.5">Pilih peran akun dan masukkan kredensial Anda.</p>
            </div>

            {{-- Segmented Role Selector --}}
            <div class="mb-6">
                <div class="grid grid-cols-3 p-1 bg-slate-100 rounded-xl border border-slate-200/80">
                    <button type="button"
                            @click="activeTab = 'masyarakat'; document.getElementById('role_field').value = 'masyarakat'"
                            :class="activeTab === 'masyarakat' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
                            class="py-2.5 px-3 rounded-lg text-xs sm:text-sm transition-all duration-200 text-center flex items-center justify-center gap-1.5 cursor-pointer">
                        <span>Warga</span>
                    </button>

                    <button type="button"
                            @click="activeTab = 'petugas'; document.getElementById('role_field').value = 'petugas'"
                            :class="activeTab === 'petugas' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
                            class="py-2.5 px-3 rounded-lg text-xs sm:text-sm transition-all duration-200 text-center flex items-center justify-center gap-1.5 cursor-pointer">
                        <span>Petugas</span>
                    </button>

                    <button type="button"
                            @click="activeTab = 'bendahara'; document.getElementById('role_field').value = 'bendahara'"
                            :class="(activeTab === 'bendahara' || activeTab === 'admin') ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
                            class="py-2.5 px-3 rounded-lg text-xs sm:text-sm transition-all duration-200 text-center flex items-center justify-center gap-1.5 cursor-pointer">
                        <span>Bendahara</span>
                    </button>
                </div>

                @error('role')
                    <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Login Form --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="role" id="role_field" value="{{ old('role', 'masyarakat') }}" :value="activeTab">

                {{-- Email Field --}}
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </div>
                        <input type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                placeholder="nama@email.com"
                                class="w-full pl-10 pr-4 py-3 bg-slate-50 hover:bg-white focus:bg-white border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition shadow-2xs @error('email') border-rose-400 ring-2 ring-rose-500/10 @enderror">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1 font-medium">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Password Field --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Kata Sandi</label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input :type="showPassword ? 'text' : 'password'"
                               id="password"
                               name="password"
                               required
                               placeholder="••••••••"
                               class="w-full pl-10 pr-11 py-3 bg-slate-50 hover:bg-white focus:bg-white border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition shadow-2xs @error('password') border-rose-400 ring-2 ring-rose-500/10 @enderror">
                        <button type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700 transition"
                                tabindex="-1">
                            <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1 font-medium">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox"
                               name="remember"
                               {{ old('remember') ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500 focus:ring-offset-0 transition cursor-pointer">
                        <span class="text-xs text-slate-600">Ingat sesi saya</span>
                    </label>
                </div>

                {{-- Submit Button --}}
                <div class="pt-2">
                    <button type="submit"
                            class="w-full py-3.5 px-6 rounded-xl font-bold text-white bg-slate-950 hover:bg-sky-600 active:scale-[0.99] transition-all duration-200 flex items-center justify-center gap-2 text-sm shadow-md hover:shadow-sky-500/20 group">
                        <span>Masuk ke Akun</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </form>

            {{-- Register Switcher --}}
            <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500">
                    Belum punya akun warga?
                    <a href="{{ route('register') }}" class="text-sky-600 hover:text-sky-700 font-bold hover:underline transition ml-1">
                        Daftar di sini
                    </a>
                </p>
            </div>
        </div>

        {{-- Bottom Copyright --}}
        <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
            <span>&copy; {{ date('Y') }} SatuAir. All rights reserved.</span>
            <span class="text-slate-400 font-mono text-[10px]">v2.4</span>
        </div>
    </div>

    {{-- ==================== RIGHT COLUMN: TEXT SHOWCASE (IMAGE REMOVED) ==================== --}}
    <div class="hidden lg:flex lg:w-1/2 relative bg-[#030712] text-white flex-col justify-between p-12 xl:p-16 overflow-hidden">
        {{-- Center/Bottom: Bold Minimalist Typography --}}
        <div class="relative z-10 my-auto py-10 max-w-lg">
            <h2 class="font-display text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-[1.08] mb-4 drop-shadow-lg">
                Air Bersih.<br>
                Lebih Cepat.<br>
                Tanpa Hambatan.
            </h2>
            <p class="text-sm sm:text-base text-slate-200/90 leading-relaxed mb-8 drop-shadow">
                Pantau pemakaian meteran secara real-time dan selesaikan pembayaran tagihan air warga dalam hitungan detik.
            </p>

        </div>

        {{-- Bottom: Subtle Clean Caption --}}
        <div class="relative z-10 flex items-center justify-between text-xs text-slate-300/70 border-t border-white/10 pt-4">
            <span>Satu platform untuk seluruh warga</span>
            <span class="font-mono text-sky-400">SATUAIR</span>
        </div>
    </div>
</div>
@endsection
