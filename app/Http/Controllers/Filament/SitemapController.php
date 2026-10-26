<?php

namespace App\Http\Controllers\Filament;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateSitemapJob;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;

class SitemapController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        GenerateSitemapJob::dispatch();

        Notification::make()
            ->title(__('Ok! Sitemap generation queued!'))
            ->success()
            ->send()
            ->sendToDatabase(auth()->user(), isEventDispatched: true);

        return redirect()->back();
    }
}
