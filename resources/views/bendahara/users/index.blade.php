@extends('layouts.app')

@section('title', 'Manajemen Pengguna — SatuAir')

@section('nav-items')
    <a href="{{ route('bendahara.dashboard') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Dashboard</a>
    <a href="{{ route('bendahara.rekap.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Rekapitulasi</a>
    <a href="{{ route('bendahara.pengguna.index') }}" class="px-3 py-2 text-sm font-semibold text-white border-b-2 border-white">Pengguna</a>
    <a href="{{ route('bendahara.complaints.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Pengaduan</a>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-[#12345A]">Manajemen Pengguna</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola data pelanggan, petugas, dan bendahara SatuAir.</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('bendahara.pengguna.index') }}" class="flex flex-col sm:flex-row sm:items-end gap-2">
            <div class="w-full sm:w-48">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Peran</label>
                <select name="role" class="w-full rounded-lg border border-slate-300 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-2 px-3 text-sm" aria-label="Filter berdasarkan peran">
                    <option value="">Semua Peran</option>
                    <option value="bendahara" {{ request('role') === 'bendahara' ? 'selected' : '' }}>Admin/Bendahara</option>
                    <option value="petugas" {{ request('role') === 'petugas' ? 'selected' : '' }}>Petugas</option>
                    <option value="masyarakat" {{ request('role') === 'masyarakat' ? 'selected' : '' }}>Masyarakat</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-[#1976D2] hover:bg-[#1565C0] text-white text-sm font-semibold rounded-lg transition">
                    Filter
                </button>
                @if(request()->filled('role'))
                    <a href="{{ route('bendahara.pengguna.index') }}" class="px-3 py-2 text-sm font-medium text-slate-600 hover:text-[#12345A]">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel Pengguna --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($users->isEmpty())
            <div class="p-12 text-center">
                <div class="text-4xl mb-3">👥</div>
                <h3 class="font-bold text-[#12345A] text-base">Tidak ada pengguna</h3>
                <p class="text-xs text-gray-500 mt-1">
                    @if(request()->filled('role'))
                        Tidak ditemukan pengguna dengan peran {{ request('role') === 'bendahara' ? 'Admin/Bendahara' : ucfirst(request('role')) }}.
                    @else
                        Belum ada data pengguna terdaftar.
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F7FA] text-gray-600 font-semibold uppercase tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">ID Pelanggan</th>
                            <th class="px-4 py-3">Peran</th>
                            <th class="px-4 py-3">Daftar</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($users as $user)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $user->name ?? '-' }}</td>
                                <td class="px-4 py-3 font-mono text-gray-600">{{ $user->email ?? '-' }}</td>
                                <td class="px-4 py-3 font-mono text-gray-600">{{ $user->customer_id ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded uppercase font-semibold text-[10px]
                                        @if($user->role === 'bendahara') bg-purple-100 text-purple-800
                                        @elseif($user->role === 'petugas') bg-blue-100 text-blue-800
                                        @else bg-green-100 text-green-800 @endif">
                                        {{ $user->role === 'bendahara' ? 'Bendahara' : ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if($user->role === 'masyarakat' || $user->role === 'petugas')
                                        <a href="{{ route('bendahara.pengguna.edit', $user->id) }}" class="px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold border border-blue-200 rounded-lg transition">
                                            Edit
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 bg-[#F5F7FA]/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
