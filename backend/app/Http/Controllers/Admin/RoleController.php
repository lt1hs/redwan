<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Role::get(['id', 'name']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(['name' => ['required', 'string', 'max:255']]);

        return DB::transaction(function () use ($request) {

            $role = Role::create(['name' => $request->name]);

            $role->syncPermissions($request->permissions);

            return $role;
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return $role->load('permissions');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate(['name' => ['required', 'string', 'max:255']]);

        return DB::transaction(function () use ($request, $role) {
            $role->syncPermissions($request->permissions);

            return $role->update(['name' => $request->name]);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        return $role->delete();
    }
}
