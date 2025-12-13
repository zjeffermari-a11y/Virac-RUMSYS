<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            $credentials = $request->only('username', 'password');

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $user->last_login = Carbon::now(); // Record the login time
                $user->save(); // Save the user

                DB::table('audit_trails')->insert([
                    'user_id' => $user->id,
                    'role_id' => $user->role_id,
                    'action' => 'User Login',
                    'module' => 'Authentication',
                    'result' => 'Success',
                    'created_at' => now(),
                ]);

                $request->session()->regenerate();
                $user->load('role');

                // Redirect based on role
                switch ($user->role->name) {
                    case 'Admin':
                        return redirect()->route('superadmin');
                    case 'Vendor':
                        return redirect()->route('vendor.dashboard');
                    case 'Staff':
                        return redirect('/staff');
                    case 'Meter Reader Clerk':
                        return redirect('/meter');
                    default:
                        return redirect('/'); // Fallback
                }
            }

            // Log failed login attempt
            try {
                DB::table('audit_trails')->insert([
                    'user_id' => null, // No user ID for failed login
                    'role_id' => 1, // Default role for failed attempts
                    'action' => 'Failed Login Attempt',
                    'module' => 'Authentication',
                    'result' => 'Failed',
                    'details' => json_encode(['username' => $request->input('username')]),
                    'created_at' => now(),
                ]);
            } catch (\Exception $e) {
                // Log error but don't fail the login response
                \Log::warning('Failed to log failed login attempt', ['error' => $e->getMessage()]);
            }

            return back()->withErrors([
                'username' => 'Invalid username or password. Please check your credentials and try again.',
            ])->withInput($request->only('username'));
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return back()->withErrors($e->errors())->withInput($request->only('username'));
        } catch (\Exception $e) {
            // Handle any other exceptions gracefully
            \Log::error('Login error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors([
                'username' => 'An error occurred during login. Please try again.',
            ])->withInput($request->only('username'));
        }
    }

    public function logout(Request $request)
        {
        $user = Auth::user();
        
        // Log logout before logging out
        if ($user) {
            DB::table('audit_trails')->insert([
                'user_id' => $user->id,
                'role_id' => $user->role_id,
                'action' => 'User Logout',
                'module' => 'Authentication',
                'result' => 'Success',
                'created_at' => now(),
            ]);
        }
        
        Auth::logout(); // Logs out the user
        // Invalidate and regenerate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login'); // Redirect to login page
    }
}
