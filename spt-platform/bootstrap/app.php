<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Support\UploadLimits;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        // Upload size is checked inside the web group (after the session
        // starts) instead of globally, so a file over the server's limit goes
        // back to the form with a message instead of Laravel's error page.
        $middleware->remove(\Illuminate\Http\Middleware\ValidatePostSize::class);
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            // Runs on every web request: an unverified email or a required but
            // missing MFA setup sends a signed-in user to the matching page.
            \App\Http\Middleware\EnsureAccountIsSecured::class,
            \Illuminate\Http\Middleware\ValidatePostSize::class,
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
    ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // A file over the server's upload limit: back to the form with a
        // message, or a JSON message for the lesson editor's uploads.
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            $message = UploadLimits::tooLargeMessage();

            if ($request->expectsJson() && ! $request->header('X-Inertia')) {
                return response()->json(['message' => $message, 'errors' => ['file' => [$message], 'image' => [$message]]], 413);
            }

            return back()->withErrors(['file' => $message]);
        });
    })->create();
