<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Update the authenticated user's profile information.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        \Log::info('Profile update request', [
            'user_id' => $user->id,
            'has_file' => $request->hasFile('profile_photo'),
            'all_data' => $request->all(),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'profile_photo' => ['nullable', 'image', 'max:2048'], // 2MB max
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            \Log::info('Processing profile photo upload');
            
            // Delete old photo if exists
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $validated['profile_photo_path'] = $path;
            
            \Log::info('Profile photo saved', ['path' => $path]);
        }

        // Remove profile_photo from validated data before update
        unset($validated['profile_photo']);
        
        $user->update($validated);

        // Get updated user with permissions and roles
        if (!$user->hasRole('Super-Admin')) {
            $permissions = $user->getAllPermissions();
        } else {
            $permissions = collect();
        }

        return response()->json([
            'message' => 'تم تحديث الملف الشخصي بنجاح',
            'user' => [
                ...$user->fresh()->toArray(),
                'permissions' => $permissions->pluck('name'),
                'roles' => $user->roles->pluck('name'),
                'profile_photo_url' => $user->profile_photo_url,
            ],
        ]);
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'كلمة المرور الحالية غير صحيحة',
                'errors' => [
                    'current_password' => ['كلمة المرور الحالية غير صحيحة']
                ]
            ], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return response()->json([
            'message' => 'تم تغيير كلمة المرور بنجاح'
        ]);
    }
}
