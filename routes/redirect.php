<?php

use Illuminate\Support\Facades\Route;

/**
 * Redirects
 *
 * These are the redirect routes for the application.
 * They are tested in the tests/Feature/RedirectTest.php file.
 */
// Public routes
Route::redirect('/home', '/');
Route::redirect('/welcome', '/');

// User account routes
Route::redirect('/account', '/account/dashboard');
Route::redirect('/dashboard', '/account/dashboard');
