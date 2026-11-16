@extends('layouts.app')

@section('title', 'Metode Pembayaran')

@section('nav-items')
    <a href="{{ route('masyarakat.dashboard') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Dashboard</a>
    <a href="{{ route('masyarakat.bills.index') }}" class="px-3 py-2 text-sm font-semibold text-white border-b-2 border-white">Tagihan Air</a>
    <a href="{{ route('masyarakat.payments.history') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Riwayat Pembayaran</a>
    <a href="{{ route('masyarakat.complaints.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Laporan Gangguan</a>
    <a href="{{ route('masyarakat.profile.show') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Profil</a>
@endsection

@section('content')
<div class="max-w-xl mx-auto pb-32"
     x-data="{
        selectedMethod: 'qris',
        baseAmount: {{ (float) $bill->total_amount }},
        adminFee: 0,
        showDetailModal: false,
        openEwalletAccordion: true,
        openVaAccordion: true,
        openQrisAccordion: true,
        
        // Modal Hubungkan
        connectModalOpen: false,
        activeWalletName: '',
        activeWalletKey: '',
        inputPhone: '{{ $user->phone ?? '081234567890' }}',
        
        // Status Terhubung & Saldo E-Wallet (Simulasi MyTelkomsel)
        wallets: {
            gopay: { name: 'GoPay', connected: true, balance: 90890, promo: 'Cashback s.d 15K' },
            shopeepay: { name: 'ShopeePay', connected: false, balance: 120000, promo: 'Diskon s.d 10%' },
            dana: { name: 'DANA', connected: false, balance: 150000, promo: 'Cashback s.d 20K' },
            ovo: { name: 'OVO', connected: false, balance: 85000, promo: 'Cashback s.d 15K' },
            linkaja: { name: 'LinkAja', connected: false, balance: 65000, promo: 'Bebas Biaya Layanan' }
        },

        init() {
            this.updateTotal();
        },

        selectMethod(key, fee = 0) {
            this.selectedMethod = key;
            this.adminFee = fee;
        },

        openConnect(key, name) {
            this.activeWalletKey = key;
            this.activeWalletName = name;
            this.connectModalOpen = true;
        },

        confirmConnect() {
            if (this.activeWalletKey && this.wallets[this.activeWalletKey]) {
                this.wallets[this.activeWalletKey].connected = true;
                this.selectedMethod = this.activeWalletKey;
                this.adminFee = 0;
            }
            this.connectModalOpen = false;
        },

        getTotal() {
            return this.baseAmount + this.adminFee;
        },

        formatRupiah(num) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num).replace('IDR', 'Rp').trim();
        }
     }">

    {{-- Top App Bar ala MyTelkomsel --}}
    <div class="flex items-center justify-between mb-4 pt-2">
        <a href="{{ route('masyarakat.bills.show', $bill->id) }}" class="inline-flex items-center gap-2 text-slate-800 hover:text-red-600 transition font-medium">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="text-lg font-extrabold text-[#0D2137] tracking-tight">Metode Pembayaran</span>
        </a>
        <span class="text-xs font-semibold text-slate-400">Periode {{ $bill->billing_period }}</span>
    </div>

    {{-- Ringkasan Tagihan Singkat --}}
    <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-200/80 mb-5 flex items-center justify-between">
        <div>
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tagihan Air Bersih</span>
            <p class="text-sm font-bold text-slate-900 mt-0.5">{{ $user->name }} &bull; {{ $bill->waterMeter->meter_number ?? 'MTR-CBD' }}</p>
            <p class="text-xs text-slate-500">Pemakaian: <strong class="text-slate-800">{{ $bill->usage_m3 }} m³</strong></p>
        </div>
        <div class="text-right">
            <span class="text-[11px] text-slate-400">Total Pokok</span>
            <p class="text-base font-extrabold text-[#0D2137]">Rp {{ number_format($bill->total_amount, 0, ',', '.') }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('masyarakat.payments.store') }}" id="paymentForm">
        @csrf
        <input type="hidden" name="water_bill_id" value="{{ $bill->id }}">
        <input type="hidden" name="payment_method" :value="selectedMethod">

        {{-- 1. SEKSI QRIS (Instan & Bebas Biaya) --}}
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 mb-4 overflow-hidden">
            <button type="button" @click="openQrisAccordion = !openQrisAccordion"
                    class="w-full p-4 flex items-center justify-between hover:bg-slate-50 transition cursor-pointer text-left">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                        QR
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">QRIS (Semua Pembayaran)</h3>
                        <p class="text-[11px] text-slate-500">BCA, Mandiri, BRI, BNI, GoPay, OVO, Dana, ShopeePay</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': openQrisAccordion }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="openQrisAccordion" x-collapse class="px-4 pb-4 pt-1 border-t border-slate-100">
                <label @click="selectMethod('qris', 0)"
                       :class="selectedMethod === 'qris' ? 'border-red-500 bg-red-50/20 ring-1 ring-red-500/20' : 'border-slate-200 hover:border-slate-300 bg-white'"
                       class="flex items-center justify-between p-3.5 rounded-xl border transition cursor-pointer">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 p-1 flex items-center justify-center">
                            <span class="text-xs font-black tracking-tighter text-slate-900">QRIS</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-slate-900">QRIS Nasional</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Bebas Biaya</span>
                            </div>
                            <p class="text-[11px] text-slate-500">Scan QR instan dari e-wallet & m-banking apa saja</p>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                         :class="selectedMethod === 'qris' ? 'border-red-600 bg-red-600' : 'border-slate-300'">
                        <div class="w-2 h-2 rounded-full bg-white" x-show="selectedMethod === 'qris'"></div>
                    </div>
                </label>
            </div>
        </div>

        {{-- 2. SEKSI E-WALLET (Ala MyTelkomsel Gambar 2 & 3) --}}
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 mb-4 overflow-hidden">
            <button type="button" @click="openEwalletAccordion = !openEwalletAccordion"
                    class="w-full p-4 flex items-center justify-between hover:bg-slate-50 transition cursor-pointer text-left">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">E-wallet</h3>
                        <p class="text-[11px] text-slate-500">GoPay, ShopeePay, DANA, OVO, LinkAja</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': openEwalletAccordion }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="openEwalletAccordion" x-collapse class="px-4 pb-4 pt-1 border-t border-slate-100 space-y-2.5">
                {{-- GoPay --}}
                <div class="p-3.5 rounded-xl border transition"
                     :class="selectedMethod === 'gopay' ? 'border-red-500 bg-red-50/20 ring-1 ring-red-500/20' : 'border-slate-200 bg-white'">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#00AED6] text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                GP
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-slate-900">GoPay</span>
                                </div>
                                <template x-if="wallets.gopay.connected">
                                    <div class="mt-0.5">
                                        <p class="text-xs font-semibold text-slate-800" x-text="'Saldo ' + formatRupiah(wallets.gopay.balance)"></p>
                                        <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200" x-text="wallets.gopay.promo"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <template x-if="wallets.gopay.connected">
                                <button type="button" @click="selectMethod('gopay', 0)"
                                        class="w-5 h-5 rounded-full border-2 flex items-center justify-center cursor-pointer"
                                        :class="selectedMethod === 'gopay' ? 'border-red-600 bg-red-600' : 'border-slate-300'">
                                    <div class="w-2 h-2 rounded-full bg-white" x-show="selectedMethod === 'gopay'"></div>
                                </button>
                            </template>
                            <template x-if="!wallets.gopay.connected">
                                <button type="button" @click="openConnect('gopay', 'GoPay')" class="text-xs font-bold text-red-600 hover:text-red-700 px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-50 transition cursor-pointer">
                                    Hubungkan
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- ShopeePay --}}
                <div class="p-3.5 rounded-xl border transition"
                     :class="selectedMethod === 'shopeepay' ? 'border-red-500 bg-red-50/20 ring-1 ring-red-500/20' : 'border-slate-200 bg-white'">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#EE4D2D] text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                SP
                            </div>
                            <div>
                                <span class="text-sm font-bold text-slate-900">ShopeePay</span>
                                <template x-if="wallets.shopeepay.connected">
                                    <div class="mt-0.5">
                                        <p class="text-xs font-semibold text-slate-800" x-text="'Saldo ' + formatRupiah(wallets.shopeepay.balance)"></p>
                                        <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200" x-text="wallets.shopeepay.promo"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <template x-if="wallets.shopeepay.connected">
                                <button type="button" @click="selectMethod('shopeepay', 0)"
                                        class="w-5 h-5 rounded-full border-2 flex items-center justify-center cursor-pointer"
                                        :class="selectedMethod === 'shopeepay' ? 'border-red-600 bg-red-600' : 'border-slate-300'">
                                    <div class="w-2 h-2 rounded-full bg-white" x-show="selectedMethod === 'shopeepay'"></div>
                                </button>
                            </template>
                            <template x-if="!wallets.shopeepay.connected">
                                <button type="button" @click="openConnect('shopeepay', 'ShopeePay')" class="text-xs font-bold text-red-600 hover:text-red-700 px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-50 transition cursor-pointer">
                                    Hubungkan
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- DANA --}}
                <div class="p-3.5 rounded-xl border transition"
                     :class="selectedMethod === 'dana' ? 'border-red-500 bg-red-50/20 ring-1 ring-red-500/20' : 'border-slate-200 bg-white'">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#118EEA] text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                DN
                            </div>
                            <div>
                                <span class="text-sm font-bold text-slate-900">DANA</span>
                                <template x-if="wallets.dana.connected">
                                    <div class="mt-0.5">
                                        <p class="text-xs font-semibold text-slate-800" x-text="'Saldo ' + formatRupiah(wallets.dana.balance)"></p>
                                        <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200" x-text="wallets.dana.promo"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <template x-if="wallets.dana.connected">
                                <button type="button" @click="selectMethod('dana', 0)"
                                        class="w-5 h-5 rounded-full border-2 flex items-center justify-center cursor-pointer"
                                        :class="selectedMethod === 'dana' ? 'border-red-600 bg-red-600' : 'border-slate-300'">
                                    <div class="w-2 h-2 rounded-full bg-white" x-show="selectedMethod === 'dana'"></div>
                                </button>
                            </template>
                            <template x-if="!wallets.dana.connected">
                                <button type="button" @click="openConnect('dana', 'DANA')" class="text-xs font-bold text-red-600 hover:text-red-700 px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-50 transition cursor-pointer">
                                    Hubungkan
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- OVO --}}
                <div class="p-3.5 rounded-xl border transition"
                     :class="selectedMethod === 'ovo' ? 'border-red-500 bg-red-50/20 ring-1 ring-red-500/20' : 'border-slate-200 bg-white'">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#4C3494] text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                OVO
                            </div>
                            <div>
                                <span class="text-sm font-bold text-slate-900">OVO</span>
                                <template x-if="wallets.ovo.connected">
                                    <div class="mt-0.5">
                                        <p class="text-xs font-semibold text-slate-800" x-text="'Saldo ' + formatRupiah(wallets.ovo.balance)"></p>
                                        <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200" x-text="wallets.ovo.promo"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <template x-if="wallets.ovo.connected">
                                <button type="button" @click="selectMethod('ovo', 0)"
                                        class="w-5 h-5 rounded-full border-2 flex items-center justify-center cursor-pointer"
                                        :class="selectedMethod === 'ovo' ? 'border-red-600 bg-red-600' : 'border-slate-300'">
                                    <div class="w-2 h-2 rounded-full bg-white" x-show="selectedMethod === 'ovo'"></div>
                                </button>
                            </template>
                            <template x-if="!wallets.ovo.connected">
                                <button type="button" @click="openConnect('ovo', 'OVO')" class="text-xs font-bold text-red-600 hover:text-red-700 px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-50 transition cursor-pointer">
                                    Hubungkan
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- LinkAja --}}
                <div class="p-3.5 rounded-xl border transition"
                     :class="selectedMethod === 'linkaja' ? 'border-red-500 bg-red-50/20 ring-1 ring-red-500/20' : 'border-slate-200 bg-white'">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#E31E24] text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                LA
                            </div>
                            <div>
                                <span class="text-sm font-bold text-slate-900">LinkAja</span>
                                <template x-if="wallets.linkaja.connected">
                                    <div class="mt-0.5">
                                        <p class="text-xs font-semibold text-slate-800" x-text="'Saldo ' + formatRupiah(wallets.linkaja.balance)"></p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <template x-if="wallets.linkaja.connected">
                                <button type="button" @click="selectMethod('linkaja', 0)"
                                        class="w-5 h-5 rounded-full border-2 flex items-center justify-center cursor-pointer"
                                        :class="selectedMethod === 'linkaja' ? 'border-red-600 bg-red-600' : 'border-slate-300'">
                                    <div class="w-2 h-2 rounded-full bg-white" x-show="selectedMethod === 'linkaja'"></div>
                                </button>
                            </template>
                            <template x-if="!wallets.linkaja.connected">
                                <button type="button" @click="openConnect('linkaja', 'LinkAja')" class="text-xs font-bold text-red-600 hover:text-red-700 px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-50 transition cursor-pointer">
                                    Hubungkan
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. SEKSI VIRTUAL ACCOUNT (VA) --}}
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 mb-4 overflow-hidden">
            <button type="button" @click="openVaAccordion = !openVaAccordion"
                    class="w-full p-4 flex items-center justify-between hover:bg-slate-50 transition cursor-pointer text-left">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Virtual Account (VA)</h3>
                        <p class="text-[11px] text-slate-500">BRI, BCA, Mandiri, BNI, BJB, BSI</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': openVaAccordion }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="openVaAccordion" x-collapse class="px-4 pb-4 pt-1 border-t border-slate-100 space-y-2">
                {{-- VA BRI --}}
                <label @click="selectMethod('va_bri', 1000)"
                       :class="selectedMethod === 'va_bri' ? 'border-red-500 bg-red-50/20 ring-1 ring-red-500/20' : 'border-slate-200 hover:border-slate-300 bg-white'"
                       class="flex items-center justify-between p-3 rounded-xl border transition cursor-pointer">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-8 rounded-md bg-[#00529C] text-white flex items-center justify-center text-[11px] font-black tracking-tight">
                            BRI
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-900">Virtual Account BRI</p>
                            <p class="text-[10px] text-slate-400">Biaya admin Rp 1.000 &bull; Otomatis terverifikasi</p>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                         :class="selectedMethod === 'va_bri' ? 'border-red-600 bg-red-600' : 'border-slate-300'">
                        <div class="w-2 h-2 rounded-full bg-white" x-show="selectedMethod === 'va_bri'"></div>
                    </div>
                </label>

                {{-- VA BCA --}}
                <label @click="selectMethod('va_bca', 1000)"
                       :class="selectedMethod === 'va_bca' ? 'border-red-500 bg-red-50/20 ring-1 ring-red-500/20' : 'border-slate-200 hover:border-slate-300 bg-white'"
                       class="flex items-center justify-between p-3 rounded-xl border transition cursor-pointer">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-8 rounded-md bg-[#003882] text-white flex items-center justify-center text-[11px] font-black tracking-tight">
                            BCA
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-900">Virtual Account BCA</p>
                            <p class="text-[10px] text-slate-400">Biaya admin Rp 1.000 &bull; Otomatis terverifikasi</p>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                         :class="selectedMethod === 'va_bca' ? 'border-red-600 bg-red-600' : 'border-slate-300'">
                        <div class="w-2 h-2 rounded-full bg-white" x-show="selectedMethod === 'va_bca'"></div>
                    </div>
                </label>

                {{-- VA Mandiri --}}
                <label @click="selectMethod('va_mandiri', 1000)"
                       :class="selectedMethod === 'va_mandiri' ? 'border-red-500 bg-red-50/20 ring-1 ring-red-500/20' : 'border-slate-200 hover:border-slate-300 bg-white'"
                       class="flex items-center justify-between p-3 rounded-xl border transition cursor-pointer">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-8 rounded-md bg-[#003D79] text-amber-300 flex items-center justify-center text-[10px] font-black tracking-tight">
                            MANDIRI
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-900">Virtual Account Mandiri (Livin')</p>
                            <p class="text-[10px] text-slate-400">Biaya admin Rp 1.000</p>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                         :class="selectedMethod === 'va_mandiri' ? 'border-red-600 bg-red-600' : 'border-slate-300'">
                        <div class="w-2 h-2 rounded-full bg-white" x-show="selectedMethod === 'va_mandiri'"></div>
                    </div>
                </label>

                {{-- VA BNI --}}
                <label @click="selectMethod('va_bni', 1000)"
                       :class="selectedMethod === 'va_bni' ? 'border-red-500 bg-red-50/20 ring-1 ring-red-500/20' : 'border-slate-200 hover:border-slate-300 bg-white'"
                       class="flex items-center justify-between p-3 rounded-xl border transition cursor-pointer">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-8 rounded-md bg-[#F15A22] text-white flex items-center justify-center text-[11px] font-black tracking-tight">
                            BNI
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-900">Virtual Account BNI</p>
                            <p class="text-[10px] text-slate-400">Biaya admin Rp 1.000</p>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                         :class="selectedMethod === 'va_bni' ? 'border-red-600 bg-red-600' : 'border-slate-300'">
                        <div class="w-2 h-2 rounded-full bg-white" x-show="selectedMethod === 'va_bni'"></div>
                    </div>
                </label>

                {{-- VA BJB --}}
                <label @click="selectMethod('va_bjb', 1000)"
                       :class="selectedMethod === 'va_bjb' ? 'border-red-500 bg-red-50/20 ring-1 ring-red-500/20' : 'border-slate-200 hover:border-slate-300 bg-white'"
                       class="flex items-center justify-between p-3 rounded-xl border transition cursor-pointer">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-8 rounded-md bg-[#0088CC] text-white flex items-center justify-center text-[11px] font-black tracking-tight">
                            BJB
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-900">Virtual Account Bank BJB</p>
                            <p class="text-[10px] text-slate-400">Bank Daerah Jawa Barat &bull; Biaya admin Rp 1.000</p>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                         :class="selectedMethod === 'va_bjb' ? 'border-red-600 bg-red-600' : 'border-slate-300'">
                        <div class="w-2 h-2 rounded-full bg-white" x-show="selectedMethod === 'va_bjb'"></div>
                    </div>
                </label>
            </div>
        </div>

        {{-- 4. SEKSI TRANSFER MANUAL --}}
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 mb-6 p-4">
            <label @click="selectMethod('bank_transfer', 0)"
                   :class="selectedMethod === 'bank_transfer' ? 'border-red-500 bg-red-50/20 ring-1 ring-red-500/20' : 'border-slate-200 hover:border-slate-300 bg-white'"
                   class="flex items-center justify-between p-3 rounded-xl border transition cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-900">Transfer Rekening BUMDes</p>
                        <p class="text-[10px] text-slate-500">Konfirmasi manual via bukti transfer</p>
                    </div>
                </div>
                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                     :class="selectedMethod === 'bank_transfer' ? 'border-red-600 bg-red-600' : 'border-slate-300'">
                    <div class="w-2 h-2 rounded-full bg-white" x-show="selectedMethod === 'bank_transfer'"></div>
                </div>
            </label>
        </div>

        {{-- FIXED BOTTOM BAR ALA MYTELKOMSEL (Gambar 2 & 3) --}}
        <div class="fixed bottom-0 inset-x-0 bg-white border-t border-slate-200/90 shadow-2xl p-4 z-40">
            <div class="max-w-xl mx-auto flex items-center justify-between gap-4">
                {{-- Kiri: Total Bayar + Toggle Dropdown --}}
                <div class="cursor-pointer" @click="showDetailModal = !showDetailModal">
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                        <span>Total Bayar</span>
                        <svg class="w-4 h-4 text-slate-600 transition-transform" :class="{ 'rotate-180': showDetailModal }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    <p class="text-xl font-extrabold text-slate-950 mt-0.5 tracking-tight" x-text="formatRupiah(getTotal())"></p>
                </div>

                {{-- Kanan: Tombol Merah Khas MyTelkomsel --}}
                <div class="w-1/2">
                    <button type="submit"
                            class="w-full py-3.5 px-6 rounded-full font-bold text-sm text-white bg-[#ED0226] hover:bg-[#D00220] active:scale-[0.98] transition-all shadow-md shadow-red-600/20 flex items-center justify-center gap-2 cursor-pointer">
                        <span>Bayar</span>
                    </button>
                </div>
            </div>

            {{-- Trust Badge --}}
            <div class="max-w-xl mx-auto mt-2 pt-2 border-t border-slate-100 flex items-center justify-center gap-1.5 text-[11px] text-slate-400">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>Pembayaran aman oleh SatuAir BUMDes Karya Mandiri</span>
            </div>
        </div>
    </form>

    {{-- MODAL DETAIL RINCIAN TAGIHAN (Pop-up saat klik arrow total bayar) --}}
    <div x-show="showDetailModal" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.outside="showDetailModal = false" class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-extrabold text-slate-900 text-sm">Rincian Pembayaran</h4>
                <button type="button" @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="space-y-2.5 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Tagihan Air Bersih (Periode {{ $bill->billing_period }})</span>
                    <span class="font-bold text-slate-900">Rp {{ number_format($bill->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Biaya Layanan / Admin</span>
                    <span class="font-bold text-slate-900" x-text="formatRupiah(adminFee)"></span>
                </div>
                <div class="border-t border-dashed border-slate-200 pt-2 flex justify-between font-extrabold text-sm text-slate-950">
                    <span>Total Pembayaran</span>
                    <span class="text-red-600" x-text="formatRupiah(getTotal())"></span>
                </div>
            </div>
            <button type="button" @click="showDetailModal = false" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl transition">
                Tutup
            </button>
        </div>
    </div>

    {{-- MODAL HUBUNGKAN NOMOR E-WALLET (Fitur permintaan user) --}}
    <div x-show="connectModalOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
        <div @click.outside="connectModalOpen = false" class="bg-white rounded-3xl p-6 w-full max-w-sm shadow-2xl border border-slate-100 space-y-4">
            <div class="text-center">
                <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-600 mx-auto flex items-center justify-center font-bold text-base mb-2">
                    📱
                </div>
                <h4 class="font-extrabold text-slate-900 text-base" x-text="'Hubungkan Akun ' + activeWalletName"></h4>
                <p class="text-xs text-slate-500 mt-1">Masukkan nomor handphone yang terdaftar pada aplikasi e-wallet Anda.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nomor Handphone</label>
                <input type="tel" x-model="inputPhone"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-mono text-sm focus:outline-none focus:border-red-500 focus:ring-4 focus:ring-red-500/10 transition">
                <p class="text-[10px] text-slate-400 mt-1">Nomor akun SatuAir Anda: {{ $user->phone ?? '-' }}</p>
            </div>

            <div class="pt-2 space-y-2">
                <button type="button" @click="confirmConnect()"
                        class="w-full py-3 bg-[#ED0226] hover:bg-[#D00220] text-white text-xs font-bold rounded-xl transition shadow-md shadow-red-600/20 cursor-pointer">
                    Hubungkan Sekarang
                </button>
                <button type="button" @click="connectModalOpen = false" class="w-full py-2.5 text-slate-500 hover:text-slate-800 text-xs font-semibold transition">
                    Batal
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
