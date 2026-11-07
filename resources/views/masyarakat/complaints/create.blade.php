@extends('layouts.app')

@section('title', 'Buat Laporan Gangguan Air')

@section('nav-items')
    <a href="{{ route('masyarakat.dashboard') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Dashboard</a>
    <a href="{{ route('masyarakat.bills.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Tagihan Air</a>
    <a href="{{ route('masyarakat.payments.history') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Riwayat Pembayaran</a>
    <a href="{{ route('masyarakat.complaints.index') }}" class="px-3 py-2 text-sm font-semibold text-white border-b-2 border-white">Laporan Gangguan</a>
    <a href="{{ route('masyarakat.profile.show') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Profil</a>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-6" x-data="{
    previewUrl: null,
    handleFile(event) {
        const file = event.target.files[0];
        if (file) {
            this.previewUrl = URL.createObjectURL(file);
        }
    }
}">

    {{-- Breadcrumb / Kembali --}}
    <div>
        <a href="{{ route('masyarakat.complaints.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-600 hover:text-[#1976D2] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Laporan
        </a>
    </div>

    {{-- Formulir Laporan --}}
    <form method="POST" action="{{ route('masyarakat.complaints.store') }}" enctype="multipart/form-data" class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-gray-100 space-y-6">
        @csrf

        {{-- Header Form --}}
        <div class="border-b border-gray-100 pb-4">
            <h1 class="text-xl sm:text-2xl font-extrabold text-[#12345A]">Buat Laporan Gangguan Air Bersih</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Laporan Anda akan segera masuk ke sistem antrean verifikasi petugas Unit Pengelola Air Bersih (PAB).
            </p>
        </div>

        {{-- Info Pelapor Terisi Otomatis --}}
        <div class="p-4 rounded-xl bg-[#EAF4FF] border border-blue-100 text-xs flex items-center justify-between">
            <div>
                <span class="text-gray-500 block text-[10px] uppercase font-semibold">Identitas Pelapor</span>
                <span class="font-bold text-[#12345A]">{{ auth()->user()->name }} ({{ auth()->user()->customer_id ?? 'Pelanggan' }})</span>
                <span class="text-gray-600 block mt-0.5">Kontak: {{ auth()->user()->phone ?? 'Belum ada nomor HP' }}</span>
            </div>
            <a href="{{ route('masyarakat.profile.show') }}" class="text-[11px] font-semibold text-[#1976D2] hover:underline">
                Ubah Profil
            </a>
        </div>

        {{-- Kategori Gangguan --}}
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                Jenis Gangguan <span class="text-rose-500">*</span>
            </label>
            <select name="category_id" required class="w-full text-sm rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-2.5 px-3">
                <option value="">-- Pilih Jenis Gangguan --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Wilayah / Dusun (Opsional) --}}
        @if(isset($areas) && $areas->isNotEmpty())
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                    Wilayah / Dusun (Opsional)
                </label>
                <select name="area_id" class="w-full text-sm rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-2.5 px-3">
                    <option value="">-- Pilih Wilayah --</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>
                            {{ $area->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- Lokasi Kejadian --}}
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                Lokasi Kejadian / Alamat Spesifik <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="location" value="{{ old('location', auth()->user()->address) }}" required placeholder="Contoh: RT 03 RW 02 Depan Masjid Al-Ikhlas / Jalur Pipa Blok A" class="w-full text-sm rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-2.5 px-3">
            <p class="text-[11px] text-gray-400 mt-1">Sebutkan patokan yang mudah ditemukan oleh petugas lapangan.</p>
            @error('location')
                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Waktu Mulai Kejadian --}}
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                Perkiraan Waktu Terjadinya Gangguan
            </label>
            <input type="datetime-local" name="occurred_at" value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}" class="w-full text-sm rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-2.5 px-3">
            @error('occurred_at')
                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Deskripsi Masalah --}}
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                Deskripsi Kendala yang Dialami <span class="text-rose-500">*</span>
            </label>
            <textarea name="description" rows="4" required placeholder="Jelaskan apa yang terjadi (contoh: air tidak mengalir sejak pagi hari, atau air keruh bercampur lumpur, kebocoran pipa sambungan depan rumah, dll.)" class="w-full text-sm rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-2.5 px-3">{{ old('description') }}</textarea>
            @error('description')
                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Foto Bukti --}}
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                Foto Bukti Kondisi Air / Pipa (Opsional)
            </label>
            <div class="border-2 border-dashed border-gray-300 hover:border-[#1976D2] rounded-2xl p-6 text-center transition cursor-pointer relative bg-slate-50/50">
                <input type="file" name="photo" accept="image/*" @change="handleFile" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                <template x-if="!previewUrl">
                    <div>
                        <div class="text-3xl mb-2">📷</div>
                        <p class="text-xs font-semibold text-gray-700">Pilih atau ambil foto dari kamera</p>
                        <p class="text-[10px] text-gray-400 mt-1">Format JPG, PNG (Maksimal 2MB)</p>
                    </div>
                </template>
                <template x-if="previewUrl">
                    <div class="space-y-2">
                        <img :src="previewUrl" class="max-h-48 mx-auto rounded-xl shadow-sm object-cover">
                        <p class="text-xs font-semibold text-emerald-600">✓ Foto terlampir</p>
                    </div>
                </template>
            </div>
            @error('photo')
                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tombol Aksi --}}
        <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-center gap-3">
            <button type="submit" class="w-full sm:flex-1 py-3 px-6 bg-[#1976D2] hover:bg-[#1565C0] text-white font-bold text-sm rounded-xl shadow-sm transition">
                Kirim Laporan Gangguan
            </button>
            <a href="{{ route('masyarakat.complaints.index') }}" class="w-full sm:w-auto py-3 px-6 bg-gray-100 hover:bg-gray-200 text-gray-700 text-center font-semibold text-xs rounded-xl transition">
                Batal
            </a>
        </div>
    </form>

</div>
@endsection
