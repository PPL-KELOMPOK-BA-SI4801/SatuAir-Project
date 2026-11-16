<!DOCTYPE html>
<html lang="id" class="scroll-smooth bg-[#030712]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SatuAir — Sistem Manajemen Air Bersih')</title>

    {{-- Google Fonts: Plus Jakarta Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        obsidian: {
                            DEFAULT: '#030712',
                            50: '#0B132B',
                            100: '#08101E',
                            200: '#050B14',
                        },
                        electric: {
                            DEFAULT: '#0066FF',
                            light: '#00D2FF',
                            glow: '#0099FF',
                            dark: '#0047AB',
                        },
                        sky: {
                            400: '#38BDF8',
                            500: '#0EA5E9',
                            600: '#0284C7',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', '-apple-system', 'sans-serif'],
                        display: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body {
            font-feature-settings: "cv02", "cv03", "cv04", "cv11";
            background-color: #030712;
            color: #FFFFFF;
        }
    </style>

    @stack('styles')
</head>
<body class="font-sans antialiased bg-[#030712] text-white selection:bg-sky-500 selection:text-white" x-data="{ mobileOpen: false }">

    {{-- ==================== MINIMALIST LUXURY NAVBAR ==================== --}}
    <nav class="fixed top-0 inset-x-0 z-50 transition-all duration-300"
         x-data="{ scrolled: false }"
         x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 30 })"
         :class="scrolled ? 'bg-[#030712]/80 backdrop-blur-xl border-b border-white/10 py-4 shadow-2xl shadow-black/80' : 'bg-transparent py-6'">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-10">
            <div class="flex items-center justify-between">

                {{-- Left: Brand Logo (ELENAVOSS style: Bold White + Light Cyan) --}}
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5 group">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center shadow-lg shadow-sky-500/20 text-white">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z" />
                        </svg>
                    </div>
                    <span class="font-bold text-2xl tracking-wider text-white">
                        SATU<span class="text-sky-400 font-light">AIR</span>
                    </span>
                </a>

                {{-- Center: Clean Navigation Links --}}
                <div class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-300">
                    <a href="#beranda" class="hover:text-white transition duration-200">Beranda</a>
                    <a href="#mengapa-kami" class="hover:text-white transition duration-200">Keunggulan</a>
                    <a href="#layanan" class="hover:text-white transition duration-200">Layanan</a>
                    <a href="#sistem" class="hover:text-white transition duration-200">Sistem</a>
                    <a href="#kontak" class="hover:text-white transition duration-200">Kontak</a>
                </div>

                {{-- Right: Auth State (Login & Register or Dashboard if already logged in) --}}
                <div class="flex items-center gap-2 sm:gap-3">
                    @auth
                        @php
                            $dashUrl = match(auth()->user()->role) {
                                'admin' => route('admin.dashboard'),
                                'petugas' => route('petugas.dashboard'),
                                default => route('masyarakat.dashboard'),
                            };
                        @endphp
                        <a href="{{ $dashUrl }}"
                           class="inline-flex items-center gap-1.5 px-3.5 sm:px-4 py-2 rounded-full bg-sky-500 hover:bg-sky-400 text-white font-semibold text-xs shadow-lg shadow-sky-500/20 active:scale-95 transition-all">
                            <span>Dashboard</span>
                            <span class="text-[10px] uppercase font-mono px-1.5 py-0.5 rounded bg-white/20 text-white">{{ auth()->user()->role }}</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-slate-400 hover:text-white transition px-2 py-2 cursor-pointer">
                                Keluar
                            </button>
                        </form>
                    @else
                        <a href="{{ url('/login') }}" class="text-xs sm:text-sm font-semibold text-slate-300 hover:text-white transition px-2.5 sm:px-3.5 py-2">
                            Login
                        </a>
                        <a href="{{ url('/register') }}"
                           class="inline-flex items-center gap-1.5 px-3.5 sm:px-5 py-2 sm:py-2.5 rounded-full bg-white text-slate-950 font-semibold text-xs hover:bg-slate-100 hover:shadow-lg hover:shadow-white/10 active:scale-95 transition-all duration-200">
                            <span>Register</span>
                        </a>
                    @endauth

                    {{-- Mobile Hamburger for navigation anchors --}}
                    <button @click="mobileOpen = !mobileOpen"
                            class="md:hidden p-2 rounded-xl text-white hover:bg-white/10 transition focus:outline-none ml-1"
                            aria-label="Toggle Menu">
                        <svg x-show="!mobileOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg x-show="mobileOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Dropdown Menu --}}
        <div x-show="mobileOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden bg-[#050B14]/95 backdrop-blur-2xl border-t border-white/10 shadow-2xl px-6 py-6 space-y-3 mt-3">
            <a href="#beranda" @click="mobileOpen = false" class="block py-2 text-sm font-medium text-slate-300 hover:text-white">Beranda</a>
            <a href="#mengapa-kami" @click="mobileOpen = false" class="block py-2 text-sm font-medium text-slate-300 hover:text-white">Keunggulan</a>
            <a href="#layanan" @click="mobileOpen = false" class="block py-2 text-sm font-medium text-slate-300 hover:text-white">Layanan</a>
            <a href="#sistem" @click="mobileOpen = false" class="block py-2 text-sm font-medium text-slate-300 hover:text-white">Sistem</a>
            <a href="#kontak" @click="mobileOpen = false" class="block py-2 text-sm font-medium text-slate-300 hover:text-white">Kontak</a>
            <div class="pt-4 border-t border-white/10 flex flex-col gap-2">
                @auth
                    <a href="{{ $dashUrl }}" class="block text-center py-2.5 text-sm font-semibold text-white bg-sky-600 rounded-full">
                        Buka Dashboard ({{ ucfirst(auth()->user()->role) }})
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-center py-2 text-xs font-semibold text-rose-400 hover:text-rose-300">
                            Keluar dari Akun
                        </button>
                    </form>
                @else
                    <a href="{{ url('/login') }}" class="block text-center py-2.5 text-sm font-semibold text-slate-950 bg-white rounded-full">
                        Login
                    </a>
                    <a href="{{ url('/register') }}" class="block text-center py-2.5 text-sm font-semibold text-white bg-white/10 rounded-full border border-white/15">
                        Register
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- ==================== MAIN CONTENT ==================== --}}
    <main>
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
