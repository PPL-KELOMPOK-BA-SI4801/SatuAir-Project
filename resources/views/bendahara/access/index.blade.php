@extends('layouts.app')

@section('title', 'Pengaturan Hak Akses')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-8">

    {{-- ── Header ── --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Pengaturan Hak Akses</h1>
        <p class="mt-1 text-sm text-gray-600">
            Atur nama, keterangan, dan izin untuk setiap peran.<br>
            Centang = fitur dapat diakses &nbsp;·&nbsp; Kosong = akses ditolak.
        </p>
    </div>

    {{-- ── Flash messages ── --}}
    @if (session('success'))
        <div class="mb-5 rounded-lg bg-green-50 border border-green-200 p-4 text-green-800 text-sm font-medium">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-5 rounded-lg bg-red-50 border border-red-200 p-4 text-red-800 text-sm">
            ⚠️ Periksa kembali isian. {{ $errors->first() }}
        </div>
    @endif

    {{-- ── Keterangan singkat cara kerja ── --}}
    <div class="mb-6 rounded-xl bg-blue-50 border border-blue-200 p-4 text-sm text-blue-800">
        <strong>Cara kerja:</strong>
        Setiap centang yang Anda simpan langsung tersimpan di database dan memengaruhi akses pengguna secara real-time.
        Tidak ada pengaturan lain yang perlu diubah.
    </div>

    <div class="space-y-6">
        @foreach ($roles as $role)
            <form
                method="POST"
                action="{{ route('bendahara.access.update', $role) }}"
                class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
            >
                @csrf
                @method('PUT')

                {{-- ── Info peran ── --}}
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Nama peran
                        <input
                            name="label"
                            value="{{ old('label', $role->label) }}"
                            required
                            maxlength="100"
                            class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </label>
                    <label class="block text-sm font-medium text-gray-700">
                        Keterangan
                        <input
                            name="description"
                            value="{{ old('description', $role->description) }}"
                            maxlength="1000"
                            class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </label>
                </div>

                <p class="mt-2 text-xs text-gray-400">
                    Kode peran: <code class="font-mono bg-gray-100 px-1 rounded">{{ $role->name }}</code>
                    {{ $role->is_system ? ' · Peran sistem' : '' }}
                </p>

                {{-- ── Daftar checkbox permission ── --}}
                <div class="mt-5 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($permissions as $group => $groupPermissions)
                        <fieldset class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                            <legend class="mb-3 text-xs font-bold uppercase tracking-wide text-gray-500 px-1">
                                {{ $group }}
                            </legend>
                            <div class="space-y-3">
                                @foreach ($groupPermissions as $permission)
                                    <label class="flex items-start gap-3 text-sm text-gray-700 cursor-pointer select-none">
                                        <input
                                            type="checkbox"
                                            name="permissions[]"
                                            value="{{ $permission->id }}"
                                            @checked($role->permissions->contains('id', $permission->id))
                                            @if($role->name === 'bendahara' && $permission->name === 'access.manage')
                                                disabled
                                            @endif
                                            class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        >

                                        {{-- Jika dinonaktifkan (access.manage untuk bendahara), kirim tetap via hidden --}}
                                        @if($role->name === 'bendahara' && $permission->name === 'access.manage')
                                            <input type="hidden" name="permissions[]" value="{{ $permission->id }}">
                                        @endif

                                        <span>
                                            {{ $permission->label }}
                                            @if($role->name === 'bendahara' && $permission->name === 'access.manage')
                                                <span class="ml-1 text-xs text-orange-500 font-medium">(wajib)</span>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                </div>

                {{-- ── Tombol simpan ── --}}
                <div class="mt-5 flex items-center justify-between">
                    <p class="text-xs text-gray-400">
                        Perubahan langsung berlaku setelah disimpan.
                    </p>
                    <button
                        type="submit"
                        class="rounded-lg bg-blue-700 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition"
                    >
                        Simpan {{ $role->label }}
                    </button>
                </div>
            </form>
        @endforeach
    </div>
</div>
@endsection
