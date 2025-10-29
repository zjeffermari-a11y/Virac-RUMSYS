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

        return back()->withErrors([
            'username' => 'Invalid username or password.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
        {
        Auth::logout(); // Logs out the user
        // Invalidate and regenerate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login'); // Redirect to login page
    }
}
