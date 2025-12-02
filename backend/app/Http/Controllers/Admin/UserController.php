<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('view-users')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $users = User::with('roles')->paginate(10);
        return response()->json($users);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('create-users')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|exists:roles,name'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);
        
        return response()->json(['data' => $user->load('roles')], 201);
    }

    public function show(User $user)
    {
        if (!auth()->user()->can('view-users')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json(['data' => $user->load('roles')]);
    }

    public function update(Request $request, User $user)
    {
        if (!auth()->user()->can('edit-users')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
        ]);

        return response()->json(['data' => $user->load('roles')]);
    }

    public function destroy(User $user)
    {
        if (!auth()->user()->can('delete-users')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        if ($user->hasRole('super-admin')) {
            return response()->json(['message' => 'Cannot delete super admin'], 403);
        }
        
        $user->delete();
        return response()->noContent();
    }

    public function assignRole(Request $request, User $user)
    {
        if (!auth()->user()->can('assign-roles')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        $user->syncRoles([$validated['role']]);
        
        return response()->json(['data' => $user->load('roles')]);
    }
}
