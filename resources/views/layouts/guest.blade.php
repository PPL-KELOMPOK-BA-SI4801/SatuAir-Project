<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk') — SatuAir</title>

    {{-- Google Fonts: Plus Jakarta Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Tailwind CSS CDN with Custom Extended Theme --}}
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
                        navy: {
                            800: '#0F2744',
                            900: '#0A192F',
                            950: '#06101E',
                        },
                        sky: {
                            500: '#0284C7',
                            600: '#0369A1',
                        },
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
        body { font-feature-settings: "cv02", "cv03", "cv04", "cv11"; }
    </style>

    @stack('styles')
</head>
<body class="font-sans text-slate-800 min-h-screen antialiased bg-slate-50 selection:bg-sky-500 selection:text-white flex flex-col justify-between">

    {{-- Flash Notifications (Toast at Top Right) --}}
    <div class="fixed top-5 right-5 z-[70] space-y-3 w-full max-w-sm pointer-events-none">
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-[-10px] scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="pointer-events-auto flex items-start gap-3 bg-white/95 backdrop-blur-md border border-emerald-200 shadow-xl shadow-emerald-950/5 rounded-2xl p-4">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="flex-1 pt-0.5">
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Berhasil</p>
                    <p class="text-sm text-slate-700 mt-0.5">{{ session('success') }}</p>
                </div>
                <button @click="show = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-[-10px] scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="pointer-events-auto flex items-start gap-3 bg-white/95 backdrop-blur-md border border-rose-200 shadow-xl shadow-rose-950/5 rounded-2xl p-4">
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="flex-1 pt-0.5">
                    <p class="text-xs font-bold uppercase tracking-wider text-rose-600">Perhatian</p>
                    <p class="text-sm text-slate-700 mt-0.5">{{ session('error') }}</p>
                </div>
                <button @click="show = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif
    </div>

    {{-- Main Content Slot (Views have full unconstrained control) --}}
    <main class="w-full flex-1 flex flex-col">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
