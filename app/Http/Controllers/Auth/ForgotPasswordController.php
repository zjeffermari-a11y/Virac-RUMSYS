<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\SmsService;
use App\Notifications\AdminVendorPasswordReset;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
class ForgotPasswordController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Handle a password reset request via SMS.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */

     public function showLinkRequestForm()
     {
         return view('auth.passwords.request');
     }
    public function sendResetSms(Request $request)
    {
        $request->validate(['username' => 'required|string|exists:users,username']);

        $vendor = User::where('username', $request->username)->where('role_id', 2)->first(); // Ensure it's a vendor

        if (!$vendor || !$vendor->getSemaphoreReadyContactNumber()) { // Use the helper method
            return back()->withErrors(['username' => 'No vendor found with this username or they do not have a registered contact number.']);
        }

        // Generate a secure temporary password
        $temporaryPassword = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Update the vendor's password and reset the 'password_changed_at' flag
        $vendor->password = Hash::make($temporaryPassword);
        $vendor->password_changed_at = null; // This forces them to change it on next login
        $vendor->save();

        // Send the temporary password via SMS
        $message = "Your temporary password for your account is: " . $temporaryPassword;
        
        // Note: Using a generic send method. Adjust if your SmsService requires a template.
        $this->smsService->send($vendor->getSemaphoreReadyContactNumber(), $message);
        
        // Notify Admins
        $admins = User::where('role_id', 1)->get();
        foreach ($admins as $admin) {
            DB::table('notifications')->insert([
                'recipient_id' => $admin->id,
                'sender_id' => $vendor->id,
                'channel' => 'in_app',
                'title' => 'Vendor Password Reset',
                'message' => json_encode([
                    'text' => 'Vendor ' . $vendor->name . ' has requested a password reset.',
                    'vendor_id' => $vendor->id,
                ]),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('status', 'A temporary password has been sent to the registered contact number.');
    }
}