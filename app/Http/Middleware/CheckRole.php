<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect('/login'); // Not logged in
        }

        $userRole = Auth::user()->role->name ?? null;

        if ($userRole !== $role) {
            abort(403, 'Unauthorized'); // Stop them
        }

        return $next($request);
    }
}
