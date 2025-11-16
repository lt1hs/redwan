<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $user = $request->user();
        
        // Create a token for the authenticated user
        $token = $user->createToken('auth_token')->plainTextToken;
        
        // Get user permissions
        if (!$user->hasRole('Super-Admin')) {
            $permissions = $user->getAllPermissions();
        } else {
            $permissions = collect();
        }
        
        return response()->json([
            'token' => $token,
            'user' => [
                ...$user->toArray(),
                'permissions' => $permissions->pluck('name'),
                'roles' => $user->roles->pluck('name'),
            ],
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }
        
        return response()->json(['message' => 'Successfully logged out']);
    }
    
    /**
     * Get the authenticated user's information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        if (!$user->hasRole('Super-Admin')) {
            $permissions = $user->getAllPermissions();
        } else {
            $permissions = collect();
        }
        
        return response()->json([
            'user' => [
                ...$user->toArray(),
                'permissions' => $permissions->pluck('name'),
                'roles' => $user->roles->pluck('name'),
            ],
        ]);
    }
} 