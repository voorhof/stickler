<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Health\Commands\RunHealthChecksCommand;
use Spatie\MediaLibrary\Conversions\Commands\RegenerateCommand;
use Spatie\ResponseCache\Middlewares\CacheResponse;
use Spatie\ResponseCache\Middlewares\DoNotCacheResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Admin Filament routes
            Route::middleware(['web', 'auth', 'verified', 'can:access admin'])
                ->prefix('admin')
                ->name('filament.admin.')
                ->group(base_path('routes/filament.php'));
            // Redirect routes
            Route::middleware(['web'])
                ->group(base_path('routes/redirect.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Spatie Response Cache: cache all successful GET requests that return text based content (HTML, JSON, etc.)
        $middleware->web(append: [
            CacheResponse::class,
        ]);

        // Spatie Response Cache: do not cache responses with the "doNotCacheResponse" middleware
        $middleware->alias([
            'doNotCacheResponse' => DoNotCacheResponse::class,
        ]);

        // Use the Spatie HttpLogger globally
        // $middleware->append(\Spatie\HttpLogger\Middlewares\HttpLogger::class);
    })
    ->withCommands([
        RegenerateCommand::class,
        RunHealthChecksCommand::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
