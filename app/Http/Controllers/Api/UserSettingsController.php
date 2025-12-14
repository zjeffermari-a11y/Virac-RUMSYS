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
            // Delete old profile picture if exists (from B2)
            if ($user->profile_picture) {
                try {
                    Storage::disk('b2')->delete($user->profile_picture);
                } catch (\Exception $e) {
                    \Log::warning('Failed to delete old profile picture from B2', [
                        'path' => $user->profile_picture,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Store the image in Backblaze B2
            $image = $request->file('profile_picture');
            $filename = 'profile_' . $user->id . '_' . time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('profile-pictures', $filename, 'b2');

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

            // Generate absolute URL for the profile picture from B2
            try {
                $visibility = config('filesystems.disks.b2.visibility', 'public');
                
                if ($visibility === 'private') {
                    // For private buckets, use temporary signed URLs (valid for 1 year)
                    $url = Storage::disk('b2')->temporaryUrl(
                        $path,
                        now()->addYear(),
                        ['ResponseContentDisposition' => 'inline']
                    );
                } else {
                    // For public buckets, use public URLs
                    $url = Storage::disk('b2')->url($path);
                    
                    // If URL is not a full URL, construct it manually using B2_URL config
                    if (!filter_var($url, FILTER_VALIDATE_URL)) {
                        $b2Url = config('filesystems.disks.b2.url');
                        if ($b2Url) {
                            // Construct full URL: B2_URL + path
                            $url = rtrim($b2Url, '/') . '/' . ltrim($path, '/');
                        } else {
                            // Fallback: use Storage URL even if relative
                            $url = Storage::disk('b2')->url($path);
                        }
                    }
                }
                
                // Ensure HTTPS (B2 URLs should already be HTTPS, but just in case)
                if (strpos($url, 'http://') === 0) {
                    $url = str_replace('http://', 'https://', $url);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to generate B2 URL', [
                    'path' => $path,
                    'error' => $e->getMessage()
                ]);
                // Fallback: try to construct URL manually
                $b2Url = config('filesystems.disks.b2.url');
                if ($b2Url) {
                    $url = rtrim($b2Url, '/') . '/' . ltrim($path, '/');
                } else {
                    throw new \Exception('B2 URL configuration is missing. Please set B2_URL in .env file.');
                }
            }
            
            // Try to get file info (wrap in try-catch to prevent failure if check fails)
            $fileExists = false;
            $fileSize = 'N/A';
            try {
                $fileExists = Storage::disk('b2')->exists($path);
                if ($fileExists) {
                    $fileSize = Storage::disk('b2')->size($path) ?? 'N/A';
                }
            } catch (\Exception $e) {
                \Log::warning('Could not verify file existence in B2', [
                    'path' => $path,
                    'error' => $e->getMessage()
                ]);
            }
            
            \Log::info('Profile picture uploaded successfully to B2', [
                'user_id' => $user->id,
                'path' => $path,
                'url' => $url,
                'app_url' => config('app.url'),
                'app_env' => config('app.env'),
                'profile_picture_field' => $user->profile_picture,
                'storage_exists' => $fileExists,
                'file_size' => $fileSize,
                'is_full_url' => filter_var($url, FILTER_VALIDATE_URL)
            ]);
            
            return response()->json([
                'message' => 'Profile picture uploaded successfully.',
                'profile_picture_url' => $url,
                'profile_picture_path' => $path,
                'success' => true
            ]);
        } catch (\Exception $e) {
            \Log::error('Profile picture upload failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'error_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Provide more helpful error message
            $errorMessage = 'Failed to upload profile picture';
            if (strpos($e->getMessage(), 'B2_URL') !== false || strpos($e->getMessage(), 'configuration') !== false) {
                $errorMessage .= ': B2 storage is not configured. Please check your .env file for B2 credentials.';
            } else {
                $errorMessage .= ': ' . $e->getMessage();
            }
            
            return response()->json([
                'message' => $errorMessage,
                'success' => false,
                'error' => $e->getMessage()
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
                Storage::disk('b2')->delete($user->profile_picture);
            } catch (\Exception $e) {
                \Log::warning('Failed to delete profile picture from B2: ' . $e->getMessage(), [
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