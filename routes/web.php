<?php

use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\TermsController;
use App\Http\Controllers\Public\WelcomeController;
use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;
use Spatie\HttpLogger\Middlewares\HttpLogger;

/**
 * GET routes with Cache-Control header
 */
Route::middleware('cache.headers:public;max_age=60;s_maxage=300;stale_while_revalidate=600;etag')->group(function () {
    /**
     * Index welcome route for the application
     */
    Route::get('/', [WelcomeController::class, 'index'])
        ->name('welcome.index');

    /**
     * ContactController routes
     */
    Route::get('/contact', [ContactController::class, 'index'])
        ->name('contact.index');

    /**
     * TermsController routes
     */
    Route::name('terms.')
        ->group(function () {
            Route::get('/algemene-voorwaarden', [TermsController::class, 'terms'])
                ->name('terms');

            Route::get('/privacy-policy', [TermsController::class, 'privacy'])
                ->name('privacy');

            Route::get('/cookie-policy', [TermsController::class, 'cookie'])
                ->name('cookie');
        });

});

/**
 * ContactController POST route
 */
Route::post('/contact', [ContactController::class, 'store'])
    ->name('contact.store')
    ->middleware([HttpLogger::class, ProtectAgainstSpam::class, 'throttle:3,1']);

/**
 * Test route
 */
if (app()->environment('local')) {
    Route::get('/test', function () {
        return view('test', [
            'headTitle' => 'TEST',
        ]);
    });
}
