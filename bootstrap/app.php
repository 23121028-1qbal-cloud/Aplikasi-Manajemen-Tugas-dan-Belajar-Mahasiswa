<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsurePasswordIsRotated;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Preferensi tema disimpan sebagai cookie biasa agar dapat ditulis dari browser
        // tanpa menjadikannya sumber otorisasi atau data aplikasi (REC-10, PROPOSED).
        $middleware->encryptCookies(except: ['theme']);

        $middleware->web(append: [
            EnsureAccountIsActive::class,
            EnsurePasswordIsRotated::class,
        ]);

        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);

        // Proxy dipercaya hanya bila dikonfigurasi eksplisit (mis. tunnel ngrok
        // atau Nginx). Tanpa ini, header X-Forwarded-* diabaikan.
        $trustedProxies = env('TRUSTED_PROXIES');

        if (filled($trustedProxies)) {
            $middleware->trustProxies(
                at: $trustedProxies === '*'
                    ? '*'
                    : array_map('trim', explode(',', (string) $trustedProxies))
            );
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
