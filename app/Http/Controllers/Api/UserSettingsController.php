<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use App\Services\AuditLogger;

class UserSettingsController extends Controller
{
    public function getRoleContacts()
    {
        $roles = ['Admin', 'Meter Reader Clerk'];
        $contacts = DB::table('users as u')
            ->join('roles as r', 'u.role_id', '=', 'r.id')
            ->whereIn('r.name', $roles)
            ->select('u.id', 'r.name as role_name', 'u.contact_number')
            ->get();

        return response()->json($contacts);
    }

    public function updateRoleContacts(Request $request)
    {
        $validated = $request->validate([
            'contacts' => 'required|array',
            'contacts.*.id' => 'required|integer|exists:users,id',
            'contacts.*.contact_number' => 'nullable|string|max:20',
        ]);

        foreach ($validated['contacts'] as $contact) {
            DB::table('users')
                ->where('id', $contact['id'])
                ->update(['contact_number' => $contact['contact_number']]);
        }

        AuditLogger::log(
            'Updated Role Contacts',
            'User Settings',
            'Success',
            ['changes' => $validated['contacts']]
        );

        return response()->json(['message' => 'Contact numbers updated successfully.']);
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('The current password is incorrect.');
                }
            }],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->mixedCase()
            ],
        ], [
            'current_password.required' => 'Current password is required.',
            'password.required' => 'New password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        AuditLogger::log(
            'Changed Password',
            'User Settings',
            'Success',
            ['user_id' => $user->id]
        );

        return response()->json(['message' => 'Password changed successfully.']);
    }

    /**
     * Upload profile picture
     */
    public function uploadProfilePicture(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
        ], [
            'profile_picture.required' => 'Please select an image to upload.',
            'profile_picture.image' => 'The file must be an image.',
            'profile_picture.mimes' => 'The image must be a jpeg, png, jpg, or gif file.',
            'profile_picture.max' => 'The image must not be larger than 2MB.',
        ]);

        try {
            // Delete old profile picture if exists
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Store the image
            $image = $request->file('profile_picture');
            $filename = 'profile_' . $user->id . '_' . time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('profile-pictures', $filename, 'public');

            // Update user record
            $user->profile_picture = $path;
            $user->save();
            
            // Refresh user model to ensure we have the latest data
            $user->refresh();

            AuditLogger::log(
                'Uploaded Profile Picture',
                'User Settings',
                'Success',
                ['user_id' => $user->id, 'path' => $path]
            );

            // Generate absolute URL for the profile picture
            $url = Storage::disk('public')->url($path);
            // Ensure it's an absolute URL - remove leading slash if present and use asset()
            $url = str_replace('//', '/', $url);
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                // If it starts with /storage, use asset() to make it absolute
                if (strpos($url, '/storage') === 0) {
                    $url = asset($url);
                } else {
                    $url = url($url);
                }
            }
            
            \Log::info('Profile picture uploaded successfully', [
                'user_id' => $user->id,
                'path' => $path,
                'url' => $url,
                'profile_picture_field' => $user->profile_picture
            ]);
            
            return response()->json([
                'message' => 'Profile picture uploaded successfully.',
                'profile_picture_url' => $url,
                'profile_picture_path' => $path
            ]);
        } catch (\Exception $e) {
            \Log::error('Profile picture upload failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'message' => 'Failed to upload profile picture: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove profile picture
     */
    public function removeProfilePicture(Request $request)
    {
        $user = Auth::user();
        
        if ($user->profile_picture) {
            try {
                Storage::disk('public')->delete($user->profile_picture);
            } catch (\Exception $e) {
                \Log::warning('Failed to delete profile picture file: ' . $e->getMessage(), [
                    'path' => $user->profile_picture,
                    'user_id' => $user->id
                ]);
            }
            
            $user->profile_picture = null;
            $user->save();
            
            // Refresh user model
            $user->refresh();

            AuditLogger::log(
                'Removed Profile Picture',
                'User Settings',
                'Success',
                ['user_id' => $user->id]
            );

            \Log::info('Profile picture removed successfully', [
                'user_id' => $user->id,
                'profile_picture_field' => $user->profile_picture
            ]);

            return response()->json(['message' => 'Profile picture removed successfully.']);
        }

        return response()->json(['message' => 'No profile picture to remove.'], 400);
    }
}