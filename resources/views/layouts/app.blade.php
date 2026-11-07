<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SatuAir') — SatuAir</title>

    {{-- Google Fonts: Plus Jakarta Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#0284C7',
                            hover: '#0369A1',
                            light: '#F0F9FF',
                        },
                        secondary: '#0F2744',
                        tertiary: '#F0F9FF',
                        'neutral-dark': '#0F172A',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .app-shell {
            width: 100%;
            max-width: 80rem;
            margin-inline: auto;
            padding-inline: 1rem;
        }
        @media (min-width: 640px) {
            .app-shell { padding-inline: 1.5rem; }
        }
        @media (min-width: 1024px) {
            .app-shell { padding-inline: 2rem; }
        }
    </style>

    @stack('styles')
</head>
<body class="bg-[#F5F7FA] font-sans text-[#263445] min-h-screen flex flex-col antialiased">

    {{-- ==================== TOP NAVBAR ==================== --}}
    <nav class="bg-[#12345A] sticky top-0 z-50 shadow-lg" x-data="{ mobileOpen: false }">
        <div class="app-shell">
            <div class="flex items-center h-16">

                {{-- Left: Logo --}}
                <div class="flex-shrink-0">
                    <a href="{{ url('/') }}" class="inline-flex">
                        <x-logo size="sm" variant="light" subtitle="" />
                    </a>
                </div>

                {{-- Center: Desktop Navigation --}}
                <div class="hidden md:flex items-center gap-2 ml-32">
                    @auth
                        @if(in_array(auth()->user()->role, ['bendahara', 'admin']))
                            @include('components.nav-bendahara')
                        @elseif(auth()->user()->role === 'petugas')
                            @include('components.nav-petugas')
                        @else
                            @include('components.nav-masyarakat')
                        @endif
                    @endauth
                </div>

                {{-- Right: Notification + User Dropdown --}}
                <div class="hidden md:flex items-center gap-3 ml-auto">
                    {{-- Notification Bell --}}
                    @if(in_array(auth()->user()?->role, ['bendahara', 'admin']))
                    <a href="{{ route('bendahara.notifications.index') }}" aria-label="Notifikasi pembayaran" class="relative text-white/80 hover:text-white transition p-2 rounded-lg hover:bg-white/10">
                        @php($unreadNotifications = auth()->user()->unreadNotifications()->count())
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if($unreadNotifications > 0)<span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>@endif
                    </a>
                    @else
                    <button class="relative text-white/80 hover:text-white transition p-2 rounded-lg hover:bg-white/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </button>
                    @endif

                    {{-- User Dropdown --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false"
                                class="flex items-center gap-2 text-white/90 hover:text-white transition px-3 py-2 rounded-lg hover:bg-white/10">
                            <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white text-sm font-semibold">
                                {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                            </div>
                            <span class="text-sm font-medium hidden lg:block">{{ Auth::user()->name ?? 'Pengguna' }}</span>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-1"
                             class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg ring-1 ring-black/5 py-2 z-50">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-semibold text-[#263445]">{{ Auth::user()->name ?? 'Pengguna' }}</p>
                                <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-xs font-medium
                                    @if(in_array(Auth::user()->role ?? '', ['bendahara', 'admin'])) bg-purple-100 text-purple-700
                                    @elseif((Auth::user()->role ?? '') === 'petugas') bg-blue-100 text-blue-700
                                    @else bg-green-100 text-green-700
                                    @endif">
                                    {{ in_array(Auth::user()->role ?? '', ['bendahara', 'admin']) ? 'Bendahara BUMDes' : ucfirst(Auth::user()->role ?? 'masyarakat') }}
                                </span>
                            </div>
                            <a href="{{ route('profil.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Profil Saya
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Mobile Hamburger --}}
                <button @click="mobileOpen = !mobileOpen" class="md:hidden ml-auto text-white p-2 rounded-lg hover:bg-white/10 transition">
                    <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden border-t border-white/10">
            <div class="px-4 py-3 space-y-1">
                @auth
                    @if(in_array(auth()->user()->role, ['bendahara', 'admin']))
                        @include('components.nav-bendahara-mobile')
                    @elseif(auth()->user()->role === 'petugas')
                        @include('components.nav-petugas-mobile')
                    @else
                        @include('components.nav-masyarakat-mobile')
                    @endif
                @endauth
            </div>
            <div class="border-t border-white/10 px-4 py-3">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-semibold">
                        {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                    </div>
                    <div>
                        <p class="text-white font-medium text-sm">{{ Auth::user()->name ?? 'Pengguna' }}</p>
                        <span class="text-white/60 text-xs">{{ in_array(Auth::user()->role ?? '', ['bendahara', 'admin']) ? 'Bendahara BUMDes' : ucfirst(Auth::user()->role ?? 'masyarakat') }}</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left text-red-300 hover:text-red-200 text-sm py-2 transition">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </nav>

    {{-- ==================== FLASH MESSAGES ==================== --}}
    <div class="fixed top-20 right-4 z-[60] space-y-3 w-full max-w-sm">
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-4"
                 class="flex items-center gap-3 bg-white border-l-4 border-[#16A34A] rounded-lg shadow-lg px-4 py-3">
                <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#16A34A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-sm text-[#263445] flex-1">{{ session('success') }}</p>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-4"
                 class="flex items-center gap-3 bg-white border-l-4 border-[#DC2626] rounded-lg shadow-lg px-4 py-3">
                <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#DC2626]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <p class="text-sm text-[#263445] flex-1">{{ session('error') }}</p>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif

        @if(session('warning'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-4"
                 class="flex items-center gap-3 bg-white border-l-4 border-[#F59E0B] rounded-lg shadow-lg px-4 py-3">
                <div class="flex-shrink-0 w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#F59E0B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <p class="text-sm text-[#263445] flex-1">{{ session('warning') }}</p>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif
    </div>

    {{-- ==================== MAIN CONTENT ==================== --}}
    <main class="flex-1">
        <div class="app-shell py-6">
            @yield('content')
        </div>
    </main>

    {{-- ==================== FOOTER ==================== --}}
    <footer class="border-t border-gray-200 bg-white">
        <div class="app-shell py-4">
            <p class="text-center text-sm text-gray-500">
                SatuAir &copy; {{ date('Y') }} &mdash; BUMDes Karya Mandiri
            </p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
