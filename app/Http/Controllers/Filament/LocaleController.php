<?php

namespace App\Http\Controllers\Filament;

use App\Http\Controllers\Controller;
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

        app()->setLocale($locale);

        return redirect()->back();
    }
}
