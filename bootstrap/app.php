<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // This is the crucial addition. It tells Laravel to use Sanctum's
        // middleware for API routes, which enables cookie-based authentication.
        $middleware->trustProxies(at: '*');
        
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Your existing middleware aliases are preserved here.
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'prevent.back.access' => \App\Http\Middleware\PreventBackAccess::class,
            'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();