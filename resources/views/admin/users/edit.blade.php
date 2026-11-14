@extends('layouts.app')

@section('title', 'Edit Pengguna — SatuAir')

@section('nav-items')
    <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Dashboard</a>
    <a href="{{ route('admin.rekap.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Rekapitulasi</a>
    <a href="{{ route('admin.pengguna.index') }}" class="px-3 py-2 text-sm font-semibold text-white border-b-2 border-white">Pengguna</a>
    <a href="{{ route('admin.complaints.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Pengaduan</a>
@endsection

@section('content')
<div class="max-w-lg mx-auto space-y-6">

    {{-- Kembali --}}
    <div>
        <a href="{{ route('admin.pengguna.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-600 hover:text-[#1976D2] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Manajemen Pengguna
        </a>
    </div>

    {{-- Form Edit --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-[#12345A]">Edit Pengguna</h2>
            <span class="px-3 py-1 rounded-full bg-green-50 text-green-700 text-xs font-bold">
                {{ ucfirst($user->role) }}
            </span>
        </div>

        <div class="text-sm text-gray-500 mb-6">
            <p class="font-medium text-gray-800">{{ $user->name }}</p>
            <p class="text-xs">Email: {{ $user->email }}</p>
        </div>

        @if($user->role === 'masyarakat')
        <form method="POST" action="{{ route('admin.pengguna.update', $user->id) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Pembacaan Meter Terbaru (m³)</label>
                <div class="flex items-center gap-2">
                    <input type="number" name="current_reading" value="{{ $latestReading->reading_value ?? $meter->latestReading?->reading_value ?? 0 }}" class="w-full rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-3 px-4 text-sm" required min="0" step="0.01">
                    <span class="text-xs text-gray-500">m³</span>
                </div>
                <p class="text-xs text-gray-400 mt-1">Pembacaan sebelumnya: {{ $latestReading ? $latestReading->reading_value : 0 }} m³</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tarif per m³ (Rp)</label>
                <input type="number" name="tarif_per_m3" value="{{ $latestBill->rate_per_m3 ?? 5000 }}" class="w-full rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-3 px-4 text-sm" required min="1000">
            </div>

            <div class="pt-4 border-t border-gray-100">
                <button type="submit" class="w-full py-3.5 px-6 bg-[#12345A] hover:bg-[#0f1e34] text-white text-sm font-bold rounded-xl shadow-sm shadow-[#12345A]/20 transition active:scale-[0.99]">
                    Simpan Perubahan
                </button>
            </div>
        </form>
        @else
        <form method="POST" action="{{ route('admin.pengguna.update', $user->id) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama</label>
                <input type="text" name="name" value="{{ $user->name }}" class="w-full rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-3 px-4 text-sm" required>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ $user->email }}" class="w-full rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-3 px-4 text-sm" required>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Telepon</label>
                <input type="text" name="phone" value="{{ $user->phone }}" class="w-full rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-3 px-4 text-sm">
            </div>

            <div class="pt-4 border-t border-gray-100">
                <button type="submit" class="w-full py-3.5 px-6 bg-[#12345A] hover:bg-[#0f1e34] text-white text-sm font-bold rounded-xl shadow-sm shadow-[#12345A]/20 transition active:scale-[0.99]">
                    Simpan Perubahan
                </button>
            </div>
        </form>
        @endif
    </div>

</div>
@endsection