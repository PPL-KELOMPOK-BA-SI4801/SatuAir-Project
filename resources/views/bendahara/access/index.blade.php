@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Pengaturan Hak Akses</h1>
        <p class="mt-1 text-sm text-gray-600">Centang izin untuk membuka fitur bagi pengguna dengan peran tersebut. Jika tidak dicentang, akses fitur ditolak.</p>
    </div>

    @if (session('success'))
        <div class="mb-5 rounded-lg bg-green-50 p-4 text-green-800">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-5 rounded-lg bg-red-50 p-4 text-red-800">Periksa kembali isian. {{ $errors->first() }}</div>
    @endif

    <div class="space-y-6">
        @foreach ($roles as $role)
            <form id="access-role-{{ $role->name }}" method="POST" action="{{ route('bendahara.access.update', $role) }}" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                @csrf
                @method('PUT')
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block text-sm font-medium text-gray-700">Nama peran
                        <input name="label" value="{{ old('label', $role->label) }}" required maxlength="100" class="mt-1 w-full rounded-lg border-gray-300">
                    </label>
                    <label class="block text-sm font-medium text-gray-700">Keterangan
                        <input name="description" value="{{ old('description', $role->description) }}" maxlength="1000" class="mt-1 w-full rounded-lg border-gray-300">
                    </label>
                </div>
                <p class="mt-3 text-xs text-gray-500">Kode peran: <code>{{ $role->name }}</code>{{ $role->is_system ? ' · Peran sistem' : '' }}</p>
                <div class="mt-5 grid gap-5 md:grid-cols-2">
                    @foreach ($permissions as $group => $groupPermissions)
                        <fieldset>
                            <legend class="mb-2 text-sm font-semibold text-gray-800">{{ $group }}</legend>
                            <div class="space-y-2">
                                @foreach ($groupPermissions as $permission)
                                    <label class="flex items-start gap-2 text-sm text-gray-700">
                                        <input id="permission-{{ $role->name }}-{{ str_replace('.', '-', $permission->name) }}" type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked($role->permissions->contains('id', $permission->id)) @disabled($permission->name === 'access.manage' && $role->name !== 'bendahara') class="mt-0.5 rounded border-gray-300">
                                        <span>{{ $permission->label }}@if ($permission->name === 'access.manage' && $role->name !== 'bendahara') <span class="text-xs text-gray-500">(khusus Bendahara)</span>@endif</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                </div>
                <div class="mt-5 flex justify-end">
                    <button class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Simpan {{ $role->label }}</button>
                </div>
            </form>
        @endforeach
    </div>
</div>
@endsection
