<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckLicense;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\ShareNavData;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        // Exempt the mobile scanner push endpoint from CSRF verification.
        // The 32-char session token in the request body serves as the shared secret.
        $middleware->validateCsrfTokens(except: [
            'pos/scanner/push',
        ]);

        $middleware->alias([
            'permission'    => CheckPermission::class,
            'check.license' => CheckLicense::class,
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            ShareNavData::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
