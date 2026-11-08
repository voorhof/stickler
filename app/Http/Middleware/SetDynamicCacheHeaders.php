<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class SetDynamicCacheHeaders
{
    /**
     * Handle an incoming request.
     *
     * Applies public Cache-Control headers, ETags, and Vary headers for anonymous guest requests,
     * while downgrading to private caching for authenticated users, active carts, or requests
     * with session notifications to prevent CDN and shared proxy cache poisoning.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string|array<string, mixed>  $options
     */
    public function handle(Request $request, Closure $next, string|array $options = []): Response
    {
        $response = $next($request);

        if (! $request->isMethodCacheable() || ! $response->isSuccessful()) {
            return $response;
        }

        if ($this->isPersonalized($request)) {
            $response->setCache([
                'private' => true,
                'max_age' => 60,
            ]);
            $response->setVary(['Cookie', 'Accept-Encoding'], false);

            return $response;
        }

        $parsedOptions = is_string($options) ? $this->parseOptions($options) : $options;

        if (empty($parsedOptions)) {
            $parsedOptions = [
                'public' => true,
                'max_age' => 60,
                's_maxage' => 300,
                'stale_while_revalidate' => 600,
                'etag' => true,
            ];
        }

        if (isset($parsedOptions['etag']) && $parsedOptions['etag'] === true) {
            $parsedOptions['etag'] = md5($response->getContent() ?: '');
        }

        if (isset($parsedOptions['last_modified'])) {
            if (is_numeric($parsedOptions['last_modified'])) {
                $parsedOptions['last_modified'] = Carbon::createFromTimestamp($parsedOptions['last_modified']);
            } else {
                $parsedOptions['last_modified'] = Carbon::parse($parsedOptions['last_modified']);
            }
        }

        $response->setCache($parsedOptions);
        $response->setVary(['Cookie', 'Accept-Encoding'], false);

        return $response;
    }

    /**
     * Determine if the request contains personalized or user-specific state.
     */
    protected function isPersonalized(Request $request): bool
    {
        if (auth()->check()) {
            return true;
        }

        if ($request->cookies->has('cart_token')) {
            return true;
        }

        if ($request->hasSession()) {
            $session = $request->session();

            if ($session->has('success') || $session->has('error') || $session->has('status') || $session->has('errors') || $session->has('alert')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse the middleware options string into an associative array.
     *
     * @return array<string, mixed>
     */
    protected function parseOptions(string $options): array
    {
        return collect(explode(';', $options))
            ->mapWithKeys(function (string $option): array {
                $data = explode('=', $option, 2);

                return [$data[0] => $data[1] ?? true];
            })
            ->all();
    }
}
