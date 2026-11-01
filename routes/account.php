<?php

use App\Http\Controllers\Account\AccountController;

/**
 * User account dashboard page
 */
Route::get('/dashboard', [AccountController::class, 'dashboard'])
    ->name('dashboard');

/**
 * User profile edit page
 */
Route::get('/profile', [AccountController::class, 'profile'])
    ->name('profile');
Route::post('/profile', [AccountController::class, 'updateProfile'])
    ->name('profile.update');

/**
 * User order history
 */
Route::get('/orders', [AccountController::class, 'orders'])
    ->name('orders.index');
Route::get('/orders/{order}', [AccountController::class, 'order'])
    ->name('orders.show');
