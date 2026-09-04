<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('cookie consent banner displays correctly on page visit', function () {
    $response = $this->get('/');

    $response->assertSuccessful()
        ->assertSee('.js-cookie-banner')
        ->assertSee(trans('cookie-consent::texts.message'), false)
        ->assertSee(trans('cookie-consent::texts.agree'), false);
});

test('cookie consent banner closes when consent cookie is set', function () {
    $cookieName = config('cookie-consent.cookie_name', 'laravel_cookie_consent');

    $response = $this->withCookie($cookieName, '1')->get('/');

    $response->assertSuccessful()
        ->assertDontSee('.c-cookie-banner');
});
