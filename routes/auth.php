<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Middleware\MergeGuestCart;
use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;
use Spatie\HttpLogger\Middlewares\HttpLogger;

Route::middleware('guest')->group(function () {
    Route::get('registreer', [RegisteredUserController::class, 'create'])
        ->name('register')
        ->middleware([HttpLogger::class, ProtectAgainstSpam::class, 'throttle:3,1']);

    Route::post('registreer', [RegisteredUserController::class, 'store'])
        ->middleware([MergeGuestCart::class]);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware([MergeGuestCart::class]);

    Route::get('wachtwoord-vergeten', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('wachtwoord-vergeten', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('wachtwoord-reset/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('wachtwoord-reset', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('email-verificatie', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('email-verificatie/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email-verificatie/verificatie', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('wachtwoord-bevestigen', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('wachtwoord-bevestigen', [ConfirmablePasswordController::class, 'store']);

    Route::put('wachtwoord-update', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
