<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageView
{
    /**
     * @var list<string>
     */
    protected array $trackedRouteNames = [
        'welcome.index',
        'contact.index',
        'terms.*',
        'posts.*',
        'projects.*',
        'pages.*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldTrack($request, $response)) {
            return $response;
        }

        PageView::create([
            'path' => '/'.mb_ltrim($request->path(), '/'),
            'route_name' => $request->route()?->getName(),
            'referrer_host' => $this->referrerHost($request),
            'visitor_hash' => $this->visitorHash($request),
            'viewed_on' => now()->toDateString(),
        ]);

        return $response;
    }

    /**
     * Only track successful GET requests to public named routes.
     */
    protected function shouldTrack(Request $request, Response $response): bool
    {
        return $request->isMethod('GET')
            && $response->getStatusCode() === 200
            && $request->routeIs(...$this->trackedRouteNames);
    }

    /**
     * Hash the visitor with a salt that changes every day, so no raw IP is stored.
     */
    protected function visitorHash(Request $request): string
    {
        return hash_hmac('sha256', $request->ip().'|'.$request->userAgent(), config('app.key').'|'.now()->toDateString());
    }

    /**
     * Store only the referrer's host, never the full URL.
     */
    protected function referrerHost(Request $request): ?string
    {
        $host = parse_url((string) $request->headers->get('referer'), PHP_URL_HOST);

        return is_string($host) && $host !== $request->getHost() ? $host : null;
    }
}
