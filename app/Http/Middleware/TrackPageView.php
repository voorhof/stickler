<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;

use function Illuminate\Support\defer;

use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Symfony\Component\HttpFoundation\Response;

class TrackPageView
{
    /**
     * @var list<string>
     */
    protected array $trackedRouteNames = [
        'welcome.index',
        'brandbook.index',
        'contact.index',
        'terms.*',
        'posts.*',
        'products.*',
        'projects.*',
        'pages.*',
        'cart.index',
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

        $pageViewAttributes = [
            'path' => '/'.mb_ltrim($request->path(), '/'),
            'route_name' => $request->route()?->getName(),
            'referrer_host' => $this->referrerHost($request),
            'visitor_hash' => $this->visitorHash($request),
            'is_bot' => $this->isBot($request),
            'viewed_on' => now()->toDateString(),
        ];

        defer(fn () => PageView::create($pageViewAttributes));

        return $response;
    }

    /**
     * Only track successful GET requests to public named routes, skipping browser prefetches and requests without a User-Agent.
     */
    protected function shouldTrack(Request $request, Response $response): bool
    {
        return $request->isMethod('GET')
            && $response->getStatusCode() === 200
            && $request->routeIs(...$this->trackedRouteNames)
            && ! $this->isPrefetch($request)
            && filled($request->userAgent());
    }

    /**
     * Determine whether the request comes from a known crawler or bot.
     */
    protected function isBot(Request $request): bool
    {
        return (new CrawlerDetect)->isCrawler($request->userAgent());
    }

    /**
     * Determine whether the browser requested the page in advance, which is not a real view.
     */
    protected function isPrefetch(Request $request): bool
    {
        return mb_strtolower((string) $request->headers->get('Purpose')) === 'prefetch'
            || mb_strtolower((string) $request->headers->get('Sec-Purpose')) === 'prefetch';
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
