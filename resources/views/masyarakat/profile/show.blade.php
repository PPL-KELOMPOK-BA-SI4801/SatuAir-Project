@extends('layouts.app')

@section('title', 'Profil Pengguna')

@section('nav-items')
    <a href="{{ route('masyarakat.dashboard') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Dashboard</a>
    <a href="{{ route('masyarakat.bills.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Tagihan Air</a>
    <a href="{{ route('masyarakat.payments.history') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Riwayat Pembayaran</a>
    <a href="{{ route('masyarakat.complaints.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Laporan Gangguan</a>
    <a href="{{ route('masyarakat.profile.show') }}" class="px-3 py-2 text-sm font-semibold text-white border-b-2 border-white">Profil</a>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-gray-100">
        <div class="flex items-center gap-4 border-b border-gray-100 pb-6 mb-6">
            <div class="w-16 h-16 rounded-2xl bg-[#1976D2] text-white flex items-center justify-center font-extrabold text-2xl shadow-md">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div>
                <h1 class="text-xl font-bold text-[#12345A]">{{ $user->name }}</h1>
                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                <span class="inline-flex items-center mt-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-[#1976D2]">
                    Pelanggan Air • {{ $user->customer_id ?? 'CUST-00001' }}
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('masyarakat.profile.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full text-sm rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-2.5 px-3">
                @error('name')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Alamat Email (Akun)</label>
                <input type="email" value="{{ $user->email }}" disabled class="w-full text-sm rounded-xl border-gray-200 bg-gray-50 text-gray-500 py-2.5 px-3 cursor-not-allowed">
                <p class="text-[11px] text-gray-400 mt-1">Email digunakan sebagai ID masuk ke aplikasi.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nomor Telepon / WhatsApp</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 08123456789" class="w-full text-sm rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-2.5 px-3">
                @error('phone')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Alamat Rumah / Titik Sambungan Air</label>
                <textarea name="address" rows="3" placeholder="Contoh: Dusun I RT 02 RW 03 No. 15" class="w-full text-sm rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-2.5 px-3">{{ old('address', $user->address) }}</textarea>
                @error('address')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="py-2.5 px-6 bg-[#1976D2] hover:bg-[#1565C0] text-white font-bold text-sm rounded-xl shadow-sm transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
