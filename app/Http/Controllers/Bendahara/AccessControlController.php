<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccessControlController extends Controller
{
    public function index(): View
    {
        $roles = Role::with('permissions')->orderBy('id')->get();
        $permissions = Permission::orderBy('group')->orderBy('label')->get()->groupBy('group');

        return view('bendahara.access.index', compact('roles', 'permissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['integer', 'distinct', 'exists:permissions,id'],
        ]);

        $permissionIds = $validated['permissions'] ?? [];
        if ($role->name === 'bendahara') {
            $accessPermission = Permission::where('name', 'access.manage')->value('id');
            if ($accessPermission) {
                $permissionIds[] = $accessPermission;
                $permissionIds = array_values(array_unique($permissionIds));
            }
        } else {
            $accessPermission = Permission::where('name', 'access.manage')->value('id');
            $permissionIds = array_values(array_diff($permissionIds, [$accessPermission]));
        }

        DB::transaction(function () use ($role, $validated, $permissionIds): void {
            $role->update([
                'label' => $validated['label'],
                'description' => $validated['description'] ?? null,
            ]);
            $role->permissions()->sync($permissionIds);
        });

        return back()->with('success', "Hak akses untuk {$role->label} berhasil diperbarui.");
    }
}
