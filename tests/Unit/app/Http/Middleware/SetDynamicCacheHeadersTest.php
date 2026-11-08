<?php

use App\Http\Middleware\SetDynamicCacheHeaders;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;

beforeEach(function () {
    $this->middleware = new SetDynamicCacheHeaders;
});

test('it applies public cache headers, etag, and vary header for guest requests', function () {
    $request = Request::create('/products');
    $response = new Response('Product catalog HTML');

    $result = $this->middleware->handle($request, fn () => $response, 'public;max_age=60;s_maxage=300;stale_while_revalidate=600;etag');

    expect($result->headers->get('Cache-Control'))->toContain('public')
        ->and($result->headers->get('Cache-Control'))->toContain('max-age=60')
        ->and($result->headers->get('Cache-Control'))->toContain('s-maxage=300')
        ->and($result->headers->get('Cache-Control'))->toContain('stale-while-revalidate=600')
        ->and($result->headers->get('ETag'))->toBe('"'.md5('Product catalog HTML').'"')
        ->and($result->headers->get('Vary'))->toContain('Cookie');
});

test('it applies default public options when no options are provided', function () {
    $request = Request::create('/posts');
    $response = new Response('Blog posts HTML');

    $result = $this->middleware->handle($request, fn () => $response);

    expect($result->headers->get('Cache-Control'))->toContain('public')
        ->and($result->headers->get('Cache-Control'))->toContain('max-age=60')
        ->and($result->headers->get('Cache-Control'))->toContain('s-maxage=300')
        ->and($result->headers->get('ETag'))->toBe('"'.md5('Blog posts HTML').'"')
        ->and($result->headers->get('Vary'))->toContain('Cookie');
});

test('it downgrades to private caching for authenticated users to prevent CDN cache poisoning', function () {
    $this->actingAs(User::factory()->create());

    $request = Request::create('/products');
    $response = new Response('Personalized product catalog HTML');

    $result = $this->middleware->handle($request, fn () => $response, 'public;max_age=60;s_maxage=300;stale_while_revalidate=600;etag');

    expect($result->headers->get('Cache-Control'))->toContain('private')
        ->and($result->headers->get('Cache-Control'))->toContain('max-age=60')
        ->and($result->headers->get('Cache-Control'))->not->toContain('public')
        ->and($result->headers->get('Cache-Control'))->not->toContain('s-maxage')
        ->and($result->headers->get('Vary'))->toContain('Cookie');
});

test('it downgrades to private caching for visitors with a cart token cookie', function () {
    $request = Request::create('/products', 'GET', [], ['cart_token' => 'guest-cart-uuid-123']);
    $response = new Response('Product catalog with cart badge HTML');

    $result = $this->middleware->handle($request, fn () => $response, 'public;max_age=60;s_maxage=300;stale_while_revalidate=600;etag');

    expect($result->headers->get('Cache-Control'))->toContain('private')
        ->and($result->headers->get('Cache-Control'))->toContain('max-age=60')
        ->and($result->headers->get('Cache-Control'))->not->toContain('public')
        ->and($result->headers->get('Cache-Control'))->not->toContain('s-maxage')
        ->and($result->headers->get('Vary'))->toContain('Cookie');
});

test('it downgrades to private caching when session contains flash notifications', function (string $flashKey) {
    $session = new Store('test_session', new ArraySessionHandler(10));
    $session->flash($flashKey, 'Flash notification message');

    $request = Request::create('/contact');
    $request->setLaravelSession($session);

    $response = new Response('Contact page with alert HTML');

    $result = $this->middleware->handle($request, fn () => $response, 'public;max_age=60;s_maxage=300;stale_while_revalidate=600;etag');

    expect($result->headers->get('Cache-Control'))->toContain('private')
        ->and($result->headers->get('Cache-Control'))->toContain('max-age=60')
        ->and($result->headers->get('Cache-Control'))->not->toContain('public')
        ->and($result->headers->get('Cache-Control'))->not->toContain('s-maxage');
})->with([
    'success' => ['success'],
    'error' => ['error'],
    'status' => ['status'],
    'errors' => ['errors'],
    'alert' => ['alert'],
]);

test('it does not modify cache headers on non-cacheable HTTP methods', function () {
    $request = Request::create('/contact', 'POST');
    $response = new Response('Created', 200);

    $result = $this->middleware->handle($request, fn () => $response, 'public;max_age=60');

    expect($result->headers->get('Cache-Control'))->not->toContain('public')
        ->and($result->headers->get('Cache-Control'))->not->toContain('max-age=60');
});

test('it does not modify cache headers on non-successful responses', function () {
    $request = Request::create('/non-existent-page');
    $response = new Response('Not Found', 404);

    $result = $this->middleware->handle($request, fn () => $response, 'public;max_age=60');

    expect($result->headers->get('Cache-Control'))->not->toContain('public')
        ->and($result->headers->get('Cache-Control'))->not->toContain('max-age=60');
});
