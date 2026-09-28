<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromUserPreference
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->locale;

        if ($locale && in_array($locale, config('app.supported_locales', []), true)) {
            $language = config("app.locale_map.$locale", explode('_', $locale)[0]);

            if (in_array($language, config('app.supported_languages', ['nl', 'en']), true)) {
                app()->setLocale($language);
            }

            Carbon::setLocale($locale);
        }

        return $next($request);
    }
}
