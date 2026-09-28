<?php

namespace App\Http\Controllers\Filament;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, config('app.supported_locales', []), true), 404);

        $user = $request->user();

        abort_unless($user !== null, 403);

        $user->update([
            'locale' => $locale,
        ]);

        $language = config("app.locale_map.$locale", explode('_', $locale)[0]);

        if (in_array($language, config('app.supported_languages', ['nl', 'en']), true)) {
            app()->setLocale($language);
        }

        Carbon::setLocale($locale);

        return redirect()->back();
    }
}
