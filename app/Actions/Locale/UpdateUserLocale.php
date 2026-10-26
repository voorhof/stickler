<?php

namespace App\Actions\Locale;

use App\Models\User;
use Carbon\Carbon;

class UpdateUserLocale
{
    /**
     * Store the user's locale and apply it to the current request.
     */
    public function handle(User $user, string $locale): void
    {
        $user->update([
            'locale' => $locale,
        ]);

        $language = config("app.locale_map.$locale", explode('_', $locale)[0]);

        if (in_array($language, config('app.supported_languages', ['nl', 'en']), true)) {
            app()->setLocale($language);
        }

        Carbon::setLocale($locale);
    }
}
