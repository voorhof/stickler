<?php

use App\Http\Controllers\Filament\LocaleController;
use App\Http\Controllers\Filament\MediaDownloadController;
use App\Http\Controllers\Filament\SitemapController;

/**
 * Admin filament routes
 */

// Locale switcher
Route::get('/locale/{locale}', [LocaleController::class, 'update'])
    ->name('locale.update');

// Media download
Route::middleware(['can:view media'])->group(function () {
    Route::get('/media/{record}/download', MediaDownloadController::class)
        ->name('resources.media.download');
});

// Sitemap generation
Route::middleware(['can:edit settings'])->group(function () {
    Route::get('/sitemap/generate', SitemapController::class)
        ->name('sitemap.generate');
});
