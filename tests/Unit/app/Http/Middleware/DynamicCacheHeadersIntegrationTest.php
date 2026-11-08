<?php

use App\Models\Cart;
use App\Models\User;

test('public routes emit public cache-control headers and vary headers for anonymous guests', function () {
    $response = $this->get(route('brandbook.index'));

    $response->assertOk();
    $cacheControl = $response->headers->get('Cache-Control');
    $vary = $response->headers->get('Vary');

    expect($cacheControl)->toContain('public')
        ->and($cacheControl)->toContain('max-age=60')
        ->and($cacheControl)->toContain('s-maxage=300')
        ->and($cacheControl)->toContain('stale-while-revalidate=600')
        ->and($response->headers->has('ETag'))->toBeTrue()
        ->and($vary)->toContain('Cookie');
});

test('public routes emit private cache-control headers without s-maxage for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('brandbook.index'));

    $response->assertOk();
    $cacheControl = $response->headers->get('Cache-Control');
    $vary = $response->headers->get('Vary');

    expect($cacheControl)->toContain('private')
        ->and($cacheControl)->toContain('max-age=60')
        ->and($cacheControl)->not->toContain('public')
        ->and($cacheControl)->not->toContain('s-maxage')
        ->and($vary)->toContain('Cookie');
});

test('public routes emit private cache-control headers without s-maxage for visitors with a cart token cookie', function () {
    $cart = Cart::factory()->create();

    $response = $this->withCookie('cart_token', $cart->cart_token)->get(route('brandbook.index'));

    $response->assertOk();
    $cacheControl = $response->headers->get('Cache-Control');
    $vary = $response->headers->get('Vary');

    expect($cacheControl)->toContain('private')
        ->and($cacheControl)->toContain('max-age=60')
        ->and($cacheControl)->not->toContain('public')
        ->and($cacheControl)->not->toContain('s-maxage')
        ->and($vary)->toContain('Cookie');
});

test('public routes emit private cache-control headers when session has flash notifications', function () {
    $response = $this->withSession(['success' => 'Form submitted successfully!'])
        ->get(route('brandbook.index'));

    $response->assertOk();
    $cacheControl = $response->headers->get('Cache-Control');

    expect($cacheControl)->toContain('private')
        ->and($cacheControl)->toContain('max-age=60')
        ->and($cacheControl)->not->toContain('public')
        ->and($cacheControl)->not->toContain('s-maxage');
});
