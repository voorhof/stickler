<?php

use App\Http\Controllers\Account\AccountController;

/**
 * User account dashboard page
 */
Route::get('/dashboard', [AccountController::class, 'dashboard'])
    ->name('dashboard');
