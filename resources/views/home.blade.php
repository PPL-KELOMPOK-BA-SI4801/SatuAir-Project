@extends('layouts.landing')

@section('title', 'SatuAir — Sistem Informasi & Layanan Air Bersih')

@section('content')
{{-- ==================== 1. HERO SECTION (REFERENCE IMAGE 1 & 4) ==================== --}}
<section id="beranda" class="relative min-h-screen flex flex-col justify-between pt-28 pb-16 lg:pt-36 lg:pb-20 overflow-hidden bg-[#030712]">
    {{-- Radiant Electric Blue Glow Atmosphere (Inspired by Reference Image 1 with Blue Palette) --}}
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        {{-- Deep Sapphire & Electric Blue Radial Aura --}}
        <div class="absolute top-1/4 right-0 lg:right-1/4 w-[650px] sm:w-[900px] h-[650px] sm:h-[900px] rounded-full blur-[120px] opacity-75"
             style="background: radial-gradient(circle, rgba(0, 102, 255, 0.85) 0%, rgba(2, 132, 199, 0.5) 40%, rgba(3, 7, 18, 0) 75%);">
        </div>
        <div class="absolute -top-32 left-0 w-[500px] h-[500px] rounded-full blur-[100px] opacity-40 bg-sky-500/30"></div>
        {{-- Subtle Grid Mesh Texture --}}
        <div class="absolute inset-0 opacity-[0.07] bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:24px_24px]"></div>
    </div>

    {{-- Main Hero Container --}}
    <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-10 relative z-10 w-full my-auto">
        <div class="grid lg:grid-cols-12 gap-10 lg:gap-14 items-center">

            {{-- Left Side: Massive Cinematic Heading (Elena Voss Style) --}}
            <div class="lg:col-span-8">
                {{-- Giant Title: Clean, Bold, High-Impact Typography --}}
                <h1 class="font-display text-4xl sm:text-6xl md:text-7xl lg:text-7xl xl:text-8xl font-extrabold tracking-tight text-white leading-[1.05] mb-6 sm:mb-8">
                    Kelola Air Bersih<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 via-blue-200 to-white">Lebih Teratur</span><br>
                    & Transparan.
                </h1>

                <p class="text-base sm:text-lg text-slate-300 max-w-xl leading-relaxed mb-8">
                    Platform digital terpadu untuk pencatatan meter akurat, pembayaran tagihan air via QRIS, dan penanganan cepat kebocoran pipa.
                </p>

                {{-- CTA Buttons --}}
                <div class="flex flex-wrap items-center gap-4">
                    @auth
                        @php
                            $dashUrl = match(auth()->user()->role) {
                                'bendahara', 'admin' => route('bendahara.dashboard'),
                                'petugas' => route('petugas.dashboard'),
                                default => route('masyarakat.dashboard'),
                            };
                            $roleLabel = in_array(auth()->user()->role, ['bendahara', 'admin']) ? 'Bendahara' : ucfirst(auth()->user()->role);
                        @endphp
                        <a href="{{ $dashUrl }}"
                           class="inline-flex items-center gap-2.5 px-7 py-3.5 rounded-full bg-white text-slate-950 font-bold text-sm sm:text-base hover:bg-slate-100 hover:shadow-xl hover:shadow-white/20 active:scale-95 transition-all duration-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Buka Dashboard ({{ $roleLabel }})</span>
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    @else
                        <a href="{{ url('/register') }}"
                           class="inline-flex items-center gap-2.5 px-7 py-3.5 rounded-full bg-white text-slate-950 font-bold text-sm sm:text-base hover:bg-slate-100 hover:shadow-xl hover:shadow-white/20 active:scale-95 transition-all duration-200">
                            <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                            <span>Daftar Akun Warga</span>
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>

                        <a href="{{ url('/login') }}"
                           class="inline-flex items-center gap-2 px-7 py-3.5 rounded-full bg-white/10 hover:bg-white/15 text-white font-semibold text-sm sm:text-base border border-white/20 backdrop-blur-md transition-all duration-200">
                            <span>Akses Portal Masuk</span>
                        </a>
                    @endauth
                </div>
            </div>

            {{-- Right Side: Editorial Meta Labels (Elena Voss Right Column Style) --}}
            <div class="lg:col-span-4 flex flex-col justify-center lg:items-end text-left lg:text-right space-y-8 sm:space-y-12">
                <div>
                    <p class="text-xs font-mono uppercase tracking-widest text-sky-400 mb-1">Cakupan Wilayah</p>
                    <p class="text-sm sm:text-base font-display font-bold text-white leading-tight">Desa Cibodas, Kecamatan Lembang, Kabupaten Bandung Barat</p>
                </div>

                <div>
                    <p class="text-xs font-mono uppercase tracking-widest text-sky-400 mb-1">Keandalan Aliran</p>
                    <p class="text-xl sm:text-2xl font-display font-bold text-white">99,6% Ketersediaan</p>
                    <p class="text-xs text-slate-400 mt-1">Sumber Mata Air Pasir Muncang</p>
                </div>

                <div>
                    <p class="text-xs font-mono uppercase tracking-widest text-sky-400 mb-1">Metode Pembayaran</p>
                    <p class="text-xl sm:text-2xl font-display font-bold text-white">QRIS, GoPay, Dana, OVO</p>
                    <p class="text-xs text-slate-400 mt-1">Bank BRI, BCA, Mandiri, BJB & semua bank</p>
                </div>
            </div>

        </div>

        {{-- Center Interactive Floating Glass Preview Card (Image 4 inspired) --}}
        <div class="mt-14 sm:mt-20">
            <div class="relative max-w-4xl mx-auto rounded-3xl bg-gradient-to-b from-white/15 to-white/5 border border-white/20 backdrop-blur-2xl p-6 sm:p-8 shadow-2xl shadow-sky-950/60 overflow-hidden">
                {{-- Top subtle glow streak --}}
                <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-32 bg-sky-400/30 rounded-full blur-2xl pointer-events-none"></div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                    {{-- User snapshot --}}
                    <div class="flex items-center gap-3.5 pb-4 md:pb-0 md:border-r border-white/10">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center font-display font-bold text-base text-white shadow-lg shadow-sky-500/30">
                            RM
                        </div>
                        <div>
                            <span class="inline-flex items-center gap-1 text-[10px] uppercase font-mono tracking-wider text-emerald-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Pelanggan Aktif
                            </span>
                            <h4 class="text-base font-bold text-white">Ibu Rina Marlina</h4>
                            <p class="text-xs text-slate-300">ID: CUST-00001 &bull; Dusun I Krajan</p>
                        </div>
                    </div>

                    {{-- Meter Usage Widget --}}
                    <div class="px-0 md:px-4 pb-4 md:pb-0 md:border-r border-white/10">
                        <div class="flex justify-between items-baseline mb-1">
                            <span class="text-xs text-slate-300">Meter Bulan Ini</span>
                            <span class="text-xs font-mono text-sky-300 font-semibold">14,2 m³</span>
                        </div>
                        <div class="w-full bg-white/10 h-2.5 rounded-full overflow-hidden mb-2">
                            <div class="bg-gradient-to-r from-sky-400 to-blue-500 h-full rounded-full" style="width: 48%"></div>
                        </div>
                        <p class="text-[11px] text-slate-400 flex items-center justify-between">
                            <span>Tarif: Rp 2.500 / m³</span>
                            <span class="text-emerald-300 font-semibold">Status: Lunas</span>
                        </p>
                    </div>

                    {{-- Quick Action & Total --}}
                    <div class="flex items-center justify-between md:justify-end gap-4 pl-0 md:pl-4">
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-slate-400">Tagihan Terbayar</p>
                            <p class="text-xl font-display font-bold text-white">Rp 35.500</p>
                        </div>
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded-full bg-white text-slate-950 font-bold text-xs hover:bg-slate-100 transition shadow-sm">
                            Cek Rincian &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ==================== 2. MENGAPA SATUAIR? (REFERENCE IMAGE 2) ==================== --}}
<section id="mengapa-kami" class="py-24 lg:py-32 bg-[#030712] border-t border-white/10 relative">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-10">

        {{-- Section Title (Exact Elena Voss "Why Work With Me?" Style) --}}
        <div class="text-center max-w-3xl mx-auto mb-20 sm:mb-24">
            <h2 class="font-display text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight mb-4">
                Mengapa Memilih SatuAir?
            </h2>
            <p class="text-base sm:text-lg text-slate-400 font-medium tracking-wide">
                Standar Baru Pengelolaan Air Bersih Desa yang Transparan & Cepat
            </p>
        </div>

        {{-- 3 Columns with Neon Cyan Glowing Outline Icons (Exact Layout of Image 2) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 lg:gap-14">

            {{-- Column 1: Rocket/Delivery (Rapid Project Delivery) --}}
            <div class="space-y-5 group">
                {{-- Neon Stroke Icon --}}
                <div class="w-12 h-12 text-sky-400 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-12 h-12 stroke-current" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>

                <h3 class="font-display text-2xl sm:text-3xl font-bold text-white tracking-tight">
                    Pencatatan Presisi & Cepat
                </h3>

                <p class="text-sm sm:text-base text-slate-400 leading-relaxed">
                    Petugas lapangan mencatat angka meter fisik langsung dari sambungan rumah warga dilengkapi bukti foto meteran, memastikan angka kubikasi akurat tanpa taksiran sepihak.
                </p>
            </div>

            {{-- Column 2: Sparkles/Aesthetics (Precision & Aesthetics) --}}
            <div class="space-y-5 group">
                {{-- Neon Stroke Icon --}}
                <div class="w-12 h-12 text-sky-400 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-12 h-12 stroke-current" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.286L13 21l-2.286-6.857L5 12l5.714-2.286L13 3z"/>
                    </svg>
                </div>

                <h3 class="font-display text-2xl sm:text-3xl font-bold text-white tracking-tight">
                    Akurasi & Transparansi Biaya
                </h3>

                <p class="text-sm sm:text-base text-slate-400 leading-relaxed">
                    Perhitungan tagihan otomatis mengacu pada tarif air BUMDes. Warga dapat melihat rincian pemakaian dan mengunduh kuitansi resmi kapan pun.
                </p>
            </div>

            {{-- Column 3: Lightning/Future (Future-Proofed Designs) --}}
            <div class="space-y-5 group">
                {{-- Neon Stroke Icon --}}
                <div class="w-12 h-12 text-sky-400 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-12 h-12 stroke-current" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>

                <h3 class="font-display text-2xl sm:text-3xl font-bold text-white tracking-tight">
                    Layanan Digital Terpadu
                </h3>

                <p class="text-sm sm:text-base text-slate-400 leading-relaxed">
                    Bayar tagihan air secara instan via QRIS bank atau dompet digital tanpa harus datang ke balai desa. Dilengkapi sistem tiket aduan pipa bocor yang langsung terhubung ke teknisi.
                </p>
            </div>

        </div>
    </div>
</section>

{{-- ==================== 3. LAYANAN & FASILITAS PRIORITAS (REFERENCE IMAGE 3) ==================== --}}
<section id="layanan" class="py-24 lg:py-32 relative overflow-hidden text-white"
         style="background: linear-gradient(180deg, #004299 0%, #005ce6 35%, #002b66 85%, #030712 100%);"
         x-data="{
             activeItem: 1
         }">

    {{-- Subtle Topographic Overlay --}}
    <div class="absolute inset-0 opacity-15 pointer-events-none">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="waves" width="60" height="60" patternUnits="userSpaceOnUse">
                    <path d="M 0 30 Q 15 15, 30 30 T 60 30" fill="none" stroke="white" stroke-width="0.8"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#waves)" />
        </svg>
    </div>

    <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-10 relative z-10">

        {{-- Section Title (Exact Elena Voss "Award Winning Work" Style: Giant White Title) --}}
        <div class="mb-14 sm:mb-20">
            <h2 class="font-display text-4xl sm:text-6xl lg:text-7xl font-extrabold text-white tracking-tight">
                Layanan & Program Prioritas
            </h2>
            <p class="text-sky-200/80 text-sm sm:text-base mt-3 max-w-xl">
                Transformasi manajemen air bersih mandiri untuk kenyamanan seluruh keluarga pelanggan.
            </p>
        </div>

        {{-- Interactive Split Layout (Image 3) --}}
        <div class="grid lg:grid-cols-12 gap-10 lg:gap-14 items-start">

            {{-- Left Column: Interactive Vertical List with Dots --}}
            <div class="lg:col-span-5 space-y-6 sm:space-y-8">

                {{-- Item 1 --}}
                <div @click="activeItem = 1"
                     class="cursor-pointer group transition-all duration-200">
                    <div class="flex items-start gap-4">
                        {{-- Radio Dot Indicator --}}
                        <div class="mt-1.5 flex items-center justify-center w-4 h-4 rounded-full border-2 transition-all duration-200"
                             :class="activeItem === 1 ? 'border-white bg-white' : 'border-white/40 group-hover:border-white/70'">
                            <div class="w-1.5 h-1.5 rounded-full"
                                 :class="activeItem === 1 ? 'bg-blue-600' : 'bg-transparent'"></div>
                        </div>
                        <div>
                            <h3 class="font-display text-xl sm:text-2xl font-bold transition-colors"
                                :class="activeItem === 1 ? 'text-white' : 'text-white/60 group-hover:text-white/90'">
                                Distribusi 4 Dusun Desa
                            </h3>
                            <p class="text-xs sm:text-sm text-sky-200/70 mt-1">
                                Pipa primer & sekunder melayani Dusun I Krajan, Dusun II Pasir Muncang, Dusun III Sukamaju, Dusun IV Mekarsari.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Item 2 --}}
                <div @click="activeItem = 2"
                     class="cursor-pointer group transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="mt-1.5 flex items-center justify-center w-4 h-4 rounded-full border-2 transition-all duration-200"
                             :class="activeItem === 2 ? 'border-white bg-white' : 'border-white/40 group-hover:border-white/70'">
                            <div class="w-1.5 h-1.5 rounded-full"
                                 :class="activeItem === 2 ? 'bg-blue-600' : 'bg-transparent'"></div>
                        </div>
                        <div>
                            <h3 class="font-display text-xl sm:text-2xl font-bold transition-colors"
                                :class="activeItem === 2 ? 'text-white' : 'text-white/60 group-hover:text-white/90'">
                                Pencatatan Meter Mobile Lapangan
                            </h3>
                            <p class="text-xs sm:text-sm text-sky-200/70 mt-1">
                                Petugas PAB mendatangi rumah warga tiap tanggal 1-5 dengan aplikasi mobile pencatat angka meter digital.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Item 3 --}}
                <div @click="activeItem = 3"
                     class="cursor-pointer group transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="mt-1.5 flex items-center justify-center w-4 h-4 rounded-full border-2 transition-all duration-200"
                             :class="activeItem === 3 ? 'border-white bg-white' : 'border-white/40 group-hover:border-white/70'">
                            <div class="w-1.5 h-1.5 rounded-full"
                                 :class="activeItem === 3 ? 'bg-blue-600' : 'bg-transparent'"></div>
                        </div>
                        <div>
                            <h3 class="font-display text-xl sm:text-2xl font-bold transition-colors"
                                :class="activeItem === 3 ? 'text-white' : 'text-white/60 group-hover:text-white/90'">
                                Pembayaran Digital QRIS & BJB
                            </h3>
                            <p class="text-xs sm:text-sm text-sky-200/70 mt-1">
                                Integrasi pembayaran modern via QRIS seluruh bank, GoPay, OVO, Dana, serta loket BUMDes.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Item 4 --}}
                <div @click="activeItem = 4"
                     class="cursor-pointer group transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="mt-1.5 flex items-center justify-center w-4 h-4 rounded-full border-2 transition-all duration-200"
                             :class="activeItem === 4 ? 'border-white bg-white' : 'border-white/40 group-hover:border-white/70'">
                            <div class="w-1.5 h-1.5 rounded-full"
                                 :class="activeItem === 4 ? 'bg-blue-600' : 'bg-transparent'"></div>
                        </div>
                        <div>
                            <h3 class="font-display text-xl sm:text-2xl font-bold transition-colors"
                                :class="activeItem === 4 ? 'text-white' : 'text-white/60 group-hover:text-white/90'">
                                Penanganan Aduan Pipa & Kerusakan
                            </h3>
                            <p class="text-xs sm:text-sm text-sky-200/70 mt-1">
                                Laporkan kebocoran pipa atau air mati dengan foto. Teknisi menerima notifikasi langsung di posko.
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column: Dynamic Featured Showcase Card (Elena Voss Right Card Style) --}}
            <div class="lg:col-span-7">
                <div class="bg-white rounded-3xl p-7 sm:p-10 shadow-2xl text-slate-900 transition-all duration-300">

                    {{-- Dynamic Content Based on activeItem --}}
                    {{-- Tab 1 Content --}}
                    <div x-show="activeItem === 1" x-transition:enter="transition ease-out duration-200">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                            <span class="text-xs font-mono font-bold text-blue-600 uppercase tracking-widest">Jalur Distribusi 01</span>
                            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-blue-50 text-blue-700">1.240+ KK Terkoneksi</span>
                        </div>
                        <h3 class="font-display text-2xl sm:text-3xl font-extrabold text-slate-900 mb-4 leading-tight">
                            Jaringan Distribusi Pipa Air Bersih BUMDes Karya Mandiri Desa Cibodas
                        </h3>
                        <p class="text-sm text-slate-600 leading-relaxed mb-6">
                            Sumber mata air Pasir Muncang dialirkan melalui pipa transmisi utama bertekanan gravitasi menuju reservoir sentral, kemudian didistribusikan ke wilayah layanan BUMDes Karya Mandiri Desa Cibodas dengan meteran induk pengukur debit otomatis.
                        </p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center mb-6">
                            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                                <p class="text-lg font-bold text-slate-900">420 KK</p>
                                <p class="text-[11px] text-slate-500">Dusun I Krajan</p>
                            </div>
                            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                                <p class="text-lg font-bold text-slate-900">310 KK</p>
                                <p class="text-[11px] text-slate-500">Dusun II P. Muncang</p>
                            </div>
                            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                                <p class="text-lg font-bold text-slate-900">280 KK</p>
                                <p class="text-[11px] text-slate-500">Dusun III Sukamaju</p>
                            </div>
                            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                                <p class="text-lg font-bold text-slate-900">230 KK</p>
                                <p class="text-[11px] text-slate-500">Dusun IV Mekarsari</p>
                            </div>
                        </div>
                        <div class="p-4 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-between text-xs text-blue-900">
                            <span class="font-semibold">Kelancaran Debit Pasokan: 99,6% Stabil</span>
                            <span class="text-blue-600 font-mono">Status: Normal</span>
                        </div>
                    </div>

                    {{-- Tab 2 Content --}}
                    <div x-show="activeItem === 2" x-cloak x-transition:enter="transition ease-out duration-200">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                            <span class="text-xs font-mono font-bold text-blue-600 uppercase tracking-widest">Sistem Pencatatan 02</span>
                            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-emerald-50 text-emerald-700">Akurasi 100%</span>
                        </div>
                        <h3 class="font-display text-2xl sm:text-3xl font-extrabold text-slate-900 mb-4 leading-tight">
                            Pencatatan Meter Digital Langsung di Depan Rumah Warga
                        </h3>
                        <p class="text-sm text-slate-600 leading-relaxed mb-6">
                            Petugas lapangan dibekali gawai khusus pencatatan. Angka meter awal dan akhir dihitung otomatis oleh sistem, lengkap dengan foto fisik water meter yang langsung masuk ke notifikasi dashboard warga.
                        </p>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-3 mb-6">
                            <div class="flex items-center justify-between text-xs font-semibold text-slate-700">
                                <span>Jadwal Catat Meter:</span>
                                <span class="text-blue-600">Tanggal 1 s/d 5 Setiap Bulan</span>
                            </div>
                            <div class="flex items-center justify-between text-xs font-semibold text-slate-700">
                                <span>Verifikasi Foto Meteran:</span>
                                <span class="text-emerald-600">Tersedia di Dashboard Warga</span>
                            </div>
                        </div>
                        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-xs text-emerald-900">
                            Mencegah sengketa taksiran air dan menjamin transparansi biaya bagi setiap kepala keluarga.
                        </div>
                    </div>

                    {{-- Tab 3 Content --}}
                    <div x-show="activeItem === 3" x-cloak x-transition:enter="transition ease-out duration-200">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                            <span class="text-xs font-mono font-bold text-blue-600 uppercase tracking-widest">Pembayaran Instan 03</span>
                            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-blue-50 text-blue-700">QRIS Multi-Bank</span>
                        </div>
                        <h3 class="font-display text-2xl sm:text-3xl font-extrabold text-slate-900 mb-4 leading-tight">
                            Bayar Tagihan Air Kapan Saja Tanpa Antre di Balai Desa
                        </h3>
                        <p class="text-sm text-slate-600 leading-relaxed mb-6">
                            Cukup scan QRIS menggunakan aplikasi perbankan (BCA, Mandiri, BRI, BJB) atau e-wallet (GoPay, OVO, Dana). Status tagihan langsung berganti Lunas seketika dengan nomor kuitansi unik.
                        </p>
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <p class="text-xs font-bold text-slate-800">Kuitansi Sah BUMDes</p>
                                <p class="text-[11px] text-slate-500 mt-1">Dapat diunduh format PDF sebagai bukti sah pembayaran.</p>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <p class="text-xs font-bold text-slate-800">Bebas Antrean</p>
                                <p class="text-[11px] text-slate-500 mt-1">Warga yang bekerja di luar kota tetap dapat melunasi tagihan keluarga.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Tab 4 Content --}}
                    <div x-show="activeItem === 4" x-cloak x-transition:enter="transition ease-out duration-200">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                            <span class="text-xs font-mono font-bold text-blue-600 uppercase tracking-widest">Layanan Aduan 04</span>
                            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-amber-50 text-amber-700">Respon &lt; 2 Jam</span>
                        </div>
                        <h3 class="font-display text-2xl sm:text-3xl font-extrabold text-slate-900 mb-4 leading-tight">
                            Pelaporan Gangguan Pipa & Debit Air Secara Real-Time
                        </h3>
                        <p class="text-sm text-slate-600 leading-relaxed mb-6">
                            Warga dapat melaporkan pipa bocor, kran induk rusak, atau air keruh dengan melampirkan foto. Laporan langsung masuk ke dashboard kerja petugas teknis dengan nomor tiket resmi.
                        </p>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2 mb-6 text-xs text-slate-600">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                <span>Status Laporan: Menunggu &rarr; Sedang Ditangani &rarr; Selesai</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                <span>Petugas teknisi lapangan stand-by setiap hari kerja</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>
</section>

{{-- ==================== 4. SISTEM TERPADU 3 PERAN (BENTO SHOWCASE) ==================== --}}
<section id="sistem" class="py-24 lg:py-32 bg-[#030712] border-t border-white/10 relative">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-10">

        <div class="text-center max-w-3xl mx-auto mb-16 sm:mb-20">
            <span class="text-xs font-mono uppercase tracking-widest text-sky-400">Ekosistem Digital</span>
            <h2 class="font-display text-3xl sm:text-5xl font-extrabold text-white tracking-tight mt-2">
                Terpadu untuk Tiga Peran Kunci
            </h2>
            <p class="text-slate-400 text-sm sm:text-base mt-3">
                Satu platform yang menyatukan masyarakat warga, petugas lapangan, dan pengurus BUMDes.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">

            {{-- Role 1: Warga --}}
            <div class="rounded-3xl bg-white/5 border border-white/10 p-8 flex flex-col justify-between hover:border-sky-500/50 hover:bg-white/[0.07] transition-all duration-300 group">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-sky-500/20 text-sky-400 flex items-center justify-center font-bold text-lg mb-6 group-hover:scale-110 transition-transform">
                        01
                    </div>
                    <h3 class="font-display text-2xl font-bold text-white mb-3">Portal Warga</h3>
                    <p class="text-sm text-slate-400 leading-relaxed mb-6">
                        Memudahkan masyarakat memantau pemakaian meteran bulanan, membayar tagihan via QRIS, mengunduh bukti kuitansi resmi, dan menyampaikan aduan gangguan.
                    </p>
                </div>
                <div class="pt-6 border-t border-white/10 flex items-center justify-between text-xs text-sky-400 font-semibold">
                    <span>Akses: rina@gmail.com</span>
                    <span>Warga &rarr;</span>
                </div>
            </div>

            {{-- Role 2: Petugas --}}
            <div class="rounded-3xl bg-white/5 border border-white/10 p-8 flex flex-col justify-between hover:border-sky-500/50 hover:bg-white/[0.07] transition-all duration-300 group">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold text-lg mb-6 group-hover:scale-110 transition-transform">
                        02
                    </div>
                    <h3 class="font-display text-2xl font-bold text-white mb-3">Portal Petugas PAB</h3>
                    <p class="text-sm text-slate-400 leading-relaxed mb-6">
                        Alat kerja mobile petugas lapangan untuk menginput angka meter, mengunggah foto water meter fisik, memantau pompa mata air, dan menindaklanjuti tiket kebocoran pipa.
                    </p>
                </div>
                <div class="pt-6 border-t border-white/10 flex items-center justify-between text-xs text-sky-400 font-semibold">
                    <span>Akses: petugas@gmail.com</span>
                    <span>Teknisi &rarr;</span>
                </div>
            </div>

            {{-- Role 3: Admin BUMDes --}}
            <div class="rounded-3xl bg-white/5 border border-white/10 p-8 flex flex-col justify-between hover:border-sky-500/50 hover:bg-white/[0.07] transition-all duration-300 group">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-lg mb-6 group-hover:scale-110 transition-transform">
                        03
                    </div>
                    <h3 class="font-display text-2xl font-bold text-white mb-3">Panel Pengurus BUMDes</h3>
                    <p class="text-sm text-slate-400 leading-relaxed mb-6">
                        Dashboard akuntansi dan pembukuan resmi BUMDes Karya Mandiri. Rekapitulasi kas pemasukan air desa, pemantauan tunggakan warga, dan laporan tahunan desa.
                    </p>
                </div>
                <div class="pt-6 border-t border-white/10 flex items-center justify-between text-xs text-sky-400 font-semibold">
                    <span>Akses: admin@gmail.com</span>
                    <span>Pengurus &rarr;</span>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ==================== 5. CTA CARD & FOOTER (REFERENCE IMAGE 5) ==================== --}}
<section id="kontak" class="py-20 lg:py-28 relative overflow-hidden bg-[#030712]">
    {{-- Radiant Ambient Blue Background Glow (Matching Image 5) --}}
    <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
        <div class="w-[700px] h-[500px] rounded-full blur-[130px] opacity-60"
             style="background: radial-gradient(circle, rgba(0, 102, 255, 0.8) 0%, rgba(2, 132, 199, 0.4) 40%, transparent 75%);"></div>
    </div>

    <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-10 relative z-10">

        {{-- Elena Voss Style Giant Rounded Dark Border Container --}}
        <div class="relative rounded-[32px] sm:rounded-[40px] border border-white/15 bg-black/70 backdrop-blur-3xl p-8 sm:p-12 lg:p-16 shadow-2xl overflow-hidden">
            {{-- Internal subtle gradient --}}
            <div class="absolute -top-32 -left-32 w-80 h-80 bg-sky-500/20 rounded-full blur-3xl pointer-events-none"></div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">

                {{-- Left Side: Brand Name & Contact Info --}}
                <div class="lg:col-span-6 space-y-8">
                    {{-- Large Brand Typography (ELENAVOSS style) --}}
                    <div>
                        <span class="font-display font-extrabold text-4xl sm:text-5xl lg:text-6xl tracking-wider text-white">
                            SATU<span class="text-sky-400 font-light">AIR</span>
                        </span>
                        <p class="text-xs sm:text-sm text-sky-200/70 mt-2 tracking-wide font-medium">
                            Sistem Manajemen Air Bersih Mandiri
                        </p>
                    </div>

                    {{-- Reach Out Contact Details (Image 5 style) --}}
                    <div class="space-y-4 pt-2">
                        <p class="text-xs font-mono uppercase tracking-widest text-slate-400">KONTAK LAYANAN</p>
                        <p class="text-xl sm:text-2xl font-display font-bold text-white hover:text-sky-400 transition cursor-pointer">
                            +62 812-3456-7890
                        </p>
                        <p class="text-sm sm:text-base text-slate-300">
                            satuair@gmail.com
                        </p>
                        <p class="text-xs text-slate-400 max-w-sm leading-relaxed">
                            Kantor Operasional PAB: Kantor BUMDes Karya Mandiri
                        </p>
                    </div>
                </div>

                {{-- Right Side: Call to Action & Pill Button --}}
                <div class="lg:col-span-6 flex flex-col justify-between lg:items-end text-left lg:text-right space-y-8">
                    <h3 class="font-display text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white leading-tight">
                        Siap Menikmati Layanan Air Digital?
                    </h3>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-full bg-white text-slate-950 font-bold text-sm sm:text-base hover:bg-slate-100 hover:shadow-xl hover:shadow-white/20 active:scale-95 transition-all duration-200">
                            <span class="w-2 h-2 rounded-full bg-slate-950"></span>
                            <span>Masuk ke Akun Sekarang</span>
                        </a>

                        <a href="{{ route('register') }}"
                           class="inline-flex items-center justify-center px-6 py-4 rounded-full bg-white/10 hover:bg-white/20 text-white font-semibold text-sm border border-white/20 transition-all duration-200">
                            <span>Daftar Warga Baru</span>
                        </a>
                    </div>

                    {{-- Circle Icon Links (Image 5 Bottom Right Style) --}}
                    <div class="flex items-center gap-3 pt-2">
                        {{-- WhatsApp --}}
                        <a href="https://wa.me/6281234567890" target="_blank"
                           class="w-10 h-10 rounded-full border border-white/20 flex items-center justify-center text-slate-300 hover:text-white hover:border-white transition"
                           title="WhatsApp Posko Air">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                            </svg>
                        </a>

                        {{-- Email --}}
                        <a href="mailto:satuair@gmail.com"
                           class="w-10 h-10 rounded-full border border-white/20 flex items-center justify-center text-slate-300 hover:text-white hover:border-white transition"
                           title="Email BUMDes">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </a>

                        {{-- Location --}}
                        <a href="#kontak"
                           class="w-10 h-10 rounded-full border border-white/20 flex items-center justify-center text-slate-300 hover:text-white hover:border-white transition"
                           title="Lokasi Balai">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </a>

                        {{-- Phone --}}
                        <a href="tel:081234567890"
                           class="w-10 h-10 rounded-full border border-white/20 flex items-center justify-center text-slate-300 hover:text-white hover:border-white transition"
                           title="Telepon Posko">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </a>
                    </div>
                </div>

            </div>
        </div>

        {{-- Bottom Copyright (Elena Voss Style) --}}
        <div class="mt-12 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-4">
            <p>Copyright &copy; SatuAir &bull; BUMDes Karya Mandiri 2026</p>
            <p class="font-mono text-slate-500 text-[11px]">Sistem Informasi Air Bersih</p>
        </div>

    </div>
</section>
@endsection
