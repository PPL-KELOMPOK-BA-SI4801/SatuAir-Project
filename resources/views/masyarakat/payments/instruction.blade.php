@extends('layouts.app')

@section('title', ($payment->paid_at ? 'Menunggu Verifikasi' : 'Menunggu Pembayaran') . ' — SatuAir')

@section('nav-items')
    <a href="{{ route('masyarakat.dashboard') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Dashboard</a>
    <a href="{{ route('masyarakat.bills.index') }}" class="px-3 py-2 text-sm font-semibold text-white border-b-2 border-white">Tagihan Air</a>
    <a href="{{ route('masyarakat.payments.history') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Riwayat Pembayaran</a>
    <a href="{{ route('masyarakat.complaints.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Laporan Gangguan</a>
    <a href="{{ route('masyarakat.profile.show') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Profil</a>
@endsection

@section('content')
<div class="max-w-xl mx-auto pb-24"
     x-data="{
        timeLeft: 1798, // 29 menit 58 detik (ala MyTelkomsel Gambar 1)
        copied: false,
        showDetail: false,
        activeGuide: 'mbanking',

        init() {
            setInterval(() => {
                if (this.timeLeft > 0) this.timeLeft--;
            }, 1000);
        },

        formatTimer() {
            const h = String(Math.floor(this.timeLeft / 3600)).padStart(2, '0');
            const m = String(Math.floor((this.timeLeft % 3600) / 60)).padStart(2, '0');
            const s = String(this.timeLeft % 60).padStart(2, '0');
            return `${h}:${m}:${s}`;
        },

        copyText(text) {
            navigator.clipboard.writeText(text);
            this.copied = true;
            setTimeout(() => this.copied = false, 2500);
        }
     }">

    {{-- Top App Bar ala MyTelkomsel Gambar 1 --}}
    <div class="flex items-center justify-between mb-4 pt-2">
        <a href="{{ route('masyarakat.bills.show', $payment->water_bill_id) }}" class="inline-flex items-center gap-2 text-slate-800 hover:text-slate-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="text-lg font-extrabold text-[#0D2137] tracking-tight">Metode Pembayaran</span>
        </a>
        <span class="text-xs font-semibold text-slate-400">Pembayaran</span>
    </div>

    {{-- Status Banner: Jam Oranye Menunggu Pembayaran (Gambar 1) --}}
    <div class="bg-white rounded-2xl p-6 shadow-xs border border-slate-200/80 mb-4 text-center">
        <div class="w-14 h-14 rounded-full bg-amber-50 text-amber-500 mx-auto flex items-center justify-center mb-3">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9" stroke-width="2.2" stroke-linecap="round"/>
                <path stroke-width="2.2" stroke-linecap="round" d="M12 7v5l3 2"/>
            </svg>
        </div>
        <h2 class="text-base font-extrabold text-slate-900 tracking-tight">{{ $payment->paid_at ? 'Menunggu Konfirmasi Admin' : 'Menunggu Pembayaran' }}</h2>

        @if(!$payment->paid_at)
        {{-- Selesaikan pembayaran dalam timer (Gambar 1) --}}
        <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
            <span class="text-slate-500 font-medium">Selesaikan pembayaran dalam</span>
            <span class="font-mono font-bold text-amber-500 text-sm" x-text="formatTimer()">00:29:58</span>
        </div>

        {{-- Warning Box (Gambar 1) --}}
        <div class="mt-3 p-3.5 rounded-xl bg-amber-50/70 border border-amber-200/80 flex items-center gap-3 text-left">
            <div class="w-6 h-6 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 text-xs font-bold">
                i
            </div>
            <p class="text-xs font-semibold text-amber-900">
                Selesaikan pembayaran sebelum waktu habis
            </p>
        </div>
        @endif
    </div>

    {{-- Detail Metode Terpilih (VA atau QRIS) --}}
    <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200/80 mb-4">
        @php
            $isVA = str_starts_with($payment->payment_method, 'va_');
            $bankName = match($payment->payment_method) {
                'va_bri'     => 'BRI',
                'va_bca'     => 'BCA',
                'va_mandiri' => 'Mandiri',
                'va_bni'     => 'BNI',
                'va_bjb'     => 'BJB',
                'va_bsi'     => 'BSI',
                default      => 'Virtual Account',
            };
            $bankColor = match($payment->payment_method) {
                'va_bri'     => '#00529C',
                'va_bca'     => '#003882',
                'va_mandiri' => '#003D79',
                'va_bni'     => '#F15A22',
                'va_bjb'     => '#0088CC',
                default      => '#12345A',
            };
        @endphp

        @if($isVA)
            {{-- Kartu Virtual Account ala MyTelkomsel Gambar 1 --}}
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-14 h-9 rounded-md text-white flex items-center justify-center font-black text-xs tracking-tight shadow-2xs"
                     style="background-color: {{ $bankColor }}">
                    {{ $bankName }}
                </div>
                <div>
                    <span class="text-xs text-slate-400 block font-medium">Virtual Account (VA)</span>
                    <span class="text-sm font-extrabold text-slate-900">{{ $bankName }}</span>
                </div>
            </div>

            <div class="pt-4">
                <span class="text-xs text-slate-500 block mb-1">Nomor Virtual Account</span>
                <div class="flex items-center justify-between">
                    <span class="text-xl sm:text-2xl font-mono font-extrabold text-slate-950 tracking-wide select-all">
                        {{ $vaNumber ?? '1019 2000 2825 7441' }}
                    </span>
                    <button type="button" @click="copyText('{{ str_replace(' ', '', $vaNumber ?? '1019200028257441') }}')"
                            class="text-xs font-bold text-sky-600 hover:text-sky-700 hover:underline px-2 py-1 transition cursor-pointer flex items-center gap-1">
                        <span x-text="copied ? 'Tersalin!' : 'Salin'">Salin</span>
                    </button>
                </div>
            </div>
        @elseif($payment->payment_method === 'qris')
            {{-- Tampilan QRIS --}}
            <div class="text-center py-2">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 text-white text-xs font-bold mb-3">
                    <span>QRIS SATUAIR</span>
                </div>
                <div class="p-4 bg-slate-900 rounded-2xl w-56 h-56 mx-auto flex flex-col items-center justify-center text-white shadow-md">
                    <svg class="w-44 h-44 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm10-2h8v8h-8V2zm2 2v4h4V4h-4zM2 14h8v8H2v-8zm2 2v4h4v-4H4zm14 0h4v4h-4v-4zm-4-2h2v2h-2v-2zm-2 2h2v2h-2v-2zm4 4h4v2h-4v-2zm-6-2h2v4h-2v-4zm2 2h2v2h-2v-2zM5 5h2v2H5V5zm12 0h2v2h-2V5zM5 17h2v2H5v-2z"/>
                    </svg>
                </div>
                <p class="text-xs font-bold text-slate-900 mt-3">BUMDes Karya Mandiri</p>
                <p class="text-[11px] font-mono text-slate-400">NMID: ID1020260192837</p>
            </div>
        @else
            {{-- Transfer Bank Manual --}}
            <div class="py-2">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Rekening Resmi BUMDes</p>
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <div>
                        <span class="text-xs font-bold text-blue-600">Bank BRI BUMDes</span>
                        <p class="font-mono font-bold text-base text-slate-900">4129-01-023849-53-1</p>
                    </div>
                    <button type="button" @click="copyText('412901023849531')" class="text-xs font-bold text-sky-600 hover:underline">
                        <span x-text="copied ? 'Tersalin!' : 'Salin'">Salin</span>
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- Total Pembayaran (Ala MyTelkomsel Gambar 1) --}}
    <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200/80 mb-4">
        <div class="flex items-center justify-between cursor-pointer" @click="showDetail = !showDetail">
            <div>
                <span class="text-xs text-slate-400 block font-medium">Total Pembayaran</span>
                @if($isVA)
                    <span class="text-[11px] text-slate-500">Sudah termasuk Rp 1.000 biaya admin {{ $bankName }}</span>
                @else
                    <span class="text-[11px] text-slate-500">Bebas biaya layanan</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                <span class="text-lg font-extrabold text-slate-950">
                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                </span>
                <svg class="w-4 h-4 text-slate-400 transition-transform" :class="{ 'rotate-180': showDetail }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>

        {{-- Rincian Expandable --}}
        <div x-show="showDetail" x-collapse class="pt-4 mt-3 border-t border-slate-100 space-y-2 text-xs">
            <div class="flex justify-between text-slate-600">
                <span>Tagihan Air (Periode {{ $payment->waterBill->billing_period }})</span>
                <span class="font-semibold text-slate-900">Rp {{ number_format($payment->waterBill->total_amount, 0, ',', '.') }}</span>
            </div>
            @if($isVA)
                <div class="flex justify-between text-slate-600">
                    <span>Biaya Layanan Admin {{ $bankName }}</span>
                    <span class="font-semibold text-slate-900">Rp 1.000</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Panduan Pembayaran (Accordion Gambar 1) --}}
    <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200/80 mb-6">
        <h3 class="text-sm font-extrabold text-slate-900 mb-3">Panduan Pembayaran</h3>

        <div class="space-y-2 text-xs">
            {{-- m-Banking --}}
            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <button type="button" @click="activeGuide = (activeGuide === 'mbanking' ? '' : 'mbanking')"
                        class="w-full p-3.5 bg-slate-50 flex items-center justify-between text-left font-bold text-slate-800 cursor-pointer">
                    <span>Mobile Banking (m-Banking)</span>
                    <svg class="w-4 h-4 text-slate-400 transition-transform" :class="{ 'rotate-180': activeGuide === 'mbanking' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="activeGuide === 'mbanking'" class="p-3.5 space-y-1.5 text-slate-600 leading-relaxed border-t border-slate-100">
                    <p>1. Buka aplikasi Mobile Banking di HP Anda.</p>
                    <p>2. Pilih menu <strong>Pembayaran / Transfer</strong> &rarr; <strong>Virtual Account</strong>.</p>
                    <p>3. Masukkan nomor VA: <strong class="text-slate-900 font-mono">{{ $vaNumber ?? '1019 2000 ...' }}</strong>.</p>
                    <p>4. Tagihan air SatuAir akan otomatis terdeteksi sebesar nominal pas.</p>
                    <p>5. Masukkan PIN transaksi Anda untuk menyelesaikan.</p>
                </div>
            </div>

            {{-- ATM --}}
            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <button type="button" @click="activeGuide = (activeGuide === 'atm' ? '' : 'atm')"
                        class="w-full p-3.5 bg-slate-50 flex items-center justify-between text-left font-bold text-slate-800 cursor-pointer">
                    <span>ATM</span>
                    <svg class="w-4 h-4 text-slate-400 transition-transform" :class="{ 'rotate-180': activeGuide === 'atm' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="activeGuide === 'atm'" class="p-3.5 space-y-1.5 text-slate-600 leading-relaxed border-t border-slate-100">
                    <p>1. Masukkan kartu ATM dan PIN.</p>
                    <p>2. Pilih menu Transaksi Lainnya &rarr; Pembayaran &rarr; Virtual Account.</p>
                    <p>3. Ketikkan nomor Virtual Account di atas.</p>
                    <p>4. Periksa kesesuaian nama pelanggan dan total bayar.</p>
                    <p>5. Tekan Ya untuk menyelesaikan transaksi.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Box (Simulasi Pembayaran Berhasil & Cek Tagihan) --}}
    <div class="bg-gradient-to-br from-sky-50 to-blue-50/50 rounded-2xl p-5 border border-sky-100 space-y-3">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
            <span class="text-xs font-bold text-slate-800">Simulasi Pembayaran Digital</span>
        </div>
        <p class="text-xs text-slate-600">
            Untuk keperluan uji coba sistem, Anda dapat langsung menekan tombol di bawah untuk menyimulasikan transfer berhasil secara seketika tanpa harus transfer sungguhan.
        </p>

        @if($payment->status === 'pending' && !$payment->paid_at)
            <form method="POST" action="{{ route('masyarakat.payments.simulate-success', $payment->id) }}">
                @csrf
                <button type="submit" class="w-full py-3.5 px-6 rounded-xl font-bold text-sm text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-600/20 active:scale-[0.99] transition cursor-pointer flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>Saya Sudah Membayar</span>
                </button>
            </form>
        @elseif($payment->status === 'pending')
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800">Pembayaran sudah dikirim. Menunggu konfirmasi admin.</div>
        @elseif($payment->status === 'verified')
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">Pembayaran telah dikonfirmasi.</div>
        @endif

        <a href="{{ route('masyarakat.bills.show', $payment->water_bill_id) }}" class="block text-center text-xs font-semibold text-slate-500 hover:text-slate-800 transition py-1">
            Kembali ke Detail Tagihan
        </a>
    </div>

</div>
@endsection

