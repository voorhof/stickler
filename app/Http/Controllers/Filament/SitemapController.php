<?php

namespace App\Http\Controllers\Filament;

use App\Http\Controllers\Controller;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;

class SitemapController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        Artisan::call('sitemap:generate');

        // Log the activity
        activity()
            ->event('updated')
            ->log('Sitemap generated');

        // Send a custom notification
        Notification::make()
            ->title(__('Ok! Sitemap generated!'))
            ->success()
            ->send()
            ->sendToDatabase(auth()->user(), isEventDispatched: true);

        return redirect()->back();
    }
}
