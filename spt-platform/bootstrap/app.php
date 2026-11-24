<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            // Runs on every web request: an unverified email or a required but
            // missing 2FA setup sends a signed-in user to the matching page.
            \App\Http\Middleware\EnsureAccountIsSecured::class,
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
    ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
