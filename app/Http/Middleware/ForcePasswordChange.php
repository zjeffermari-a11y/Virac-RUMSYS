<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Check if user exists and is a vendor
        if ($user && $user->role && $user->role->name === 'Vendor') {
            
            // Check if password has never been changed (password_changed_at is null)
            if (is_null($user->password_changed_at)) {
                
                // Allow access to the change password routes and logout
                if ($request->routeIs('vendor.password.form') || 
                    $request->routeIs('vendor.password.update') || 
                    $request->routeIs('logout')) {
                    return $next($request);
                }

                // For all other routes, redirect to change password page
                return redirect()->route('vendor.password.form')
                    ->with('warning', 'You must change your password before continuing.');
            }
        }

        return $next($request);
    }
}