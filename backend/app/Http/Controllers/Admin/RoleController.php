<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('view-roles')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $roles = Role::with('permissions')->get();
        return response()->json(['data' => $roles]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('create-roles')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|unique:roles',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $role = Role::create(['name' => $validated['name']]);
        
        if (isset($validated['permissions'])) {
            $role->givePermissionTo($validated['permissions']);
        }
        
        return response()->json(['data' => $role->load('permissions')], 201);
    }

    public function show(Role $role)
    {
        if (!auth()->user()->can('view-roles')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json(['data' => $role->load('permissions')]);
    }

    public function update(Request $request, Role $role)
    {
        if (!auth()->user()->can('edit-roles')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $role->update(['name' => $validated['name']]);
        
        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }
        
        return response()->json(['data' => $role->load('permissions')]);
    }

    public function destroy(Role $role)
    {
        if (!auth()->user()->can('delete-roles')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        if (in_array($role->name, ['super-admin', 'admin', 'employee'])) {
            return response()->json(['message' => 'Cannot delete system roles'], 403);
        }
        
        $role->delete();
        return response()->noContent();
    }

    public function permissions()
    {
        $permissions = Permission::all();
        return response()->json(['data' => $permissions]);
    }
}
