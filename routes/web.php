<?php

use App\Http\Controllers\Public\BrandbookController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\PostController;
use App\Http\Controllers\Public\ProductController;
use App\Http\Controllers\Public\ProjectController;
use App\Http\Controllers\Public\TermsController;
use App\Http\Controllers\Public\WelcomeController;
use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;
use Spatie\HttpLogger\Middlewares\HttpLogger;

/**
 * GET routes with Cache-Control header and TrackPageView middleware
 *
 * Note: Add extra added routes you want to keep counts for,
 * to $trackedRouteNames inside the TrackPageView class.
 */
Route::middleware('cache.headers:public;max_age=60;s_maxage=300;stale_while_revalidate=600;etag')->group(function () {
    /**
     * Index welcome route for the application
     */
    Route::get('/', [WelcomeController::class, 'index'])
        ->name('welcome.index');

    /**
     * BrandbookController routes
     */
    Route::get('/brandbook', [BrandbookController::class, 'index'])
        ->name('brandbook.index');

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

    /**
     * PostController routes
     */
    Route::get('/posts', [PostController::class, 'index'])
        ->name('posts.index');
    Route::get('/posts/{post}', [PostController::class, 'show'])
        ->name('posts.show');

    /**
     * ProductController routes
     */
    Route::get('/products', [ProductController::class, 'index'])
        ->name('products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])
        ->name('products.show');

    /**
     * ProjectController routes
     */
    Route::get('/projects', [ProjectController::class, 'index'])
        ->name('projects.index');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])
        ->name('projects.show');

    /**
     * PageController routes (put these routes last)
     */
    Route::get('/pages', [PageController::class, 'index'])
        ->name('pages.index');
    Route::get('/{page}', [PageController::class, 'show'])
        ->name('pages.show');
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
    Route::get('/test/test', function () {
        return view('test', [
            'headTitle' => 'TEST',
        ]);
    });
}
