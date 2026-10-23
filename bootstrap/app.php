<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Outermost, so it also sees the 419 response of the CSRF middleware
        $middleware->web(prepend: [
            \App\Http\Middleware\ExpireStaleSessionCookies::class,
        ]);

        $middleware->web(append: [
            //\App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // The "login" route no longer exists: staff and companies have their own
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin', 'admin/*')) {
                return route('admin.login');
            }

            // "{region}" is not a model yet: the binding runs after this middleware
            $region = $request->route('region');

            if ($region !== null) {
                return route('account.login', ['region' => $region instanceof \App\Models\Region ? $region->subdomain : $region]);
            }

            return route('home');
        });

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
