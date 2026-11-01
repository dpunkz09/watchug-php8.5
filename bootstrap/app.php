<?php

use App\Http\Middleware\LanguageMiddleware;
use App\Http\Middleware\RedirectToInstaller;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append([
            \App\Http\Middleware\RedirectToInstaller::class,
            \App\Http\Middleware\LanguageMiddleware::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\LanguageMiddleware::class,
        ]);

        $middleware->api(append: [
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
        ]);

        $middleware->preventRequestForgery(except: [
            'webhooks/*',
        ]);

        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'auth.admin' => \App\Http\Middleware\IsAdmin::class,
            'demo' => \App\Http\Middleware\DemoAdmin::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'hotlink' => \App\Http\Middleware\HotlinkProtectionMiddleware::class,
            'signed' => \App\Http\Middleware\ValidateSignature::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
