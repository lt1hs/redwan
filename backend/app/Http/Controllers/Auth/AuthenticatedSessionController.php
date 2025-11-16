<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();
        
        // Get the authenticated user
        $user = $request->user();
        
        // Create a new token for the user
        $token = $user->createToken('auth-token')->plainTextToken;
        
        // If the request wants JSON, return the token and user
        if (!$user->hasRole('Super-Admin')) {
            $permissions = $user->getAllPermissions();
        } else {
            $permissions = collect();
        }
        
        return response()->json([
            'token' => $token,
            'user' => [
                ...$user->toArray(),
                "permissions" => $permissions->pluck('name'),
                "roles" => $user->roles->pluck('name'),
            ]
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        if ($request->user()) {
            // Revoke the token that was used to authenticate the current request
            $request->user()->currentAccessToken()->delete();
        }
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
