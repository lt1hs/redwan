<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('can:edit people')->only('update');
    //     $this->middleware('can:delete people')->only('destroy');
    //     $this->middleware('can:show people')->only(['index', 'show']);
    // }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return User::select(['id', 'name', 'email'])->whereNot('email', 'info@dijlah.org')->get();
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique(User::class),
                ],
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            if ($request->has('roles'))
                $user->assignRole($request->roles);
            if ($request->has('permissions'))
                $user->syncPermissions($request->permissions);

            return $user;
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['roles', 'permissions']);
        $user->roles = $user->roles->map(function ($item) {
            return $item['name'];
        });
        $user->unsetRelation('roles');
        return $user;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        return DB::transaction(function () use ($request, $user) {
            $rules = [
                'name' => ['required', 'string', 'max:255'],

                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id),
                ]
            ];
            if ($request->filled('password')) {
                $rules['password'] = ['required', 'confirmed', Password::defaults()];
            }
            $request->validate($rules);


            $user->name = $request->name;
            $user->email = $request->email;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->syncRoles($request->roles);
            $user->syncPermissions($request->permissions);
            return $user->save();
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
    }
}
