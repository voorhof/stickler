<?php

namespace App\Http\Controllers\Filament;

use App\Actions\Sitemap\RegenerateSitemap;
use App\Http\Controllers\Controller;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;

class SitemapController extends Controller
{
    public function __invoke(RegenerateSitemap $regenerateSitemap): RedirectResponse
    {
        $regenerateSitemap->handle();

        // Send a custom notification
        Notification::make()
            ->title(__('Ok! Sitemap generated!'))
            ->success()
            ->send()
            ->sendToDatabase(auth()->user(), isEventDispatched: true);

        return redirect()->back();
    }
}
