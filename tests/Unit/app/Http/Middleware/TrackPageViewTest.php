<?php

use App\Models\PageView;
use App\Models\Product;
use App\Models\User;

test('a public page view is stored through deferred processing', function () {
    $this->get('/contact')->assertOk();

    $this->assertDatabaseCount('page_views', 1);
});

test('a public page view is stored without the raw ip address', function () {
    $this->withoutDefer();

    $this->get('/contact')->assertOk();

    $pageView = PageView::query()->first();

    expect(PageView::count())->toBe(1)
        ->and($pageView->path)->toBe('/contact')
        ->and($pageView->route_name)->toBe('contact.index')
        ->and($pageView->visitor_hash)->toHaveLength(64)
        ->and($pageView->visitor_hash)->not->toContain('127.0.0.1');
});

test('the visitor hash changes from one day to the next', function () {
    $this->withoutDefer();

    $this->get('/contact')->assertOk();
    $hashToday = PageView::query()->value('visitor_hash');

    $this->travel(1)->days();
    $this->get('/contact')->assertOk();
    $hashTomorrow = PageView::query()->latest('id')->value('visitor_hash');

    expect($hashTomorrow)->not->toBe($hashToday);
});

test('non public routes are not tracked', function () {
    $this->post('/contact')->assertRedirect();

    expect(PageView::count())->toBe(0);
});

test('the cart page is tracked as a page view', function () {
    $this->withoutDefer();

    $this->get('/cart')->assertOk();

    $pageView = PageView::query()->first();

    expect(PageView::count())->toBe(1)
        ->and($pageView->path)->toBe('/cart')
        ->and($pageView->route_name)->toBe('cart.index');
});

test('cart actions are not tracked as page views', function () {
    $this->withoutDefer();

    User::factory()->create();

    $product = Product::factory()->create();

    $this->post(route('cart.add', $product))
        ->assertRedirect();

    expect(PageView::count())->toBe(0);
});

test('a crawler visit is stored as a bot', function () {
    $this->withoutDefer();

    $this->withHeaders(['User-Agent' => 'Googlebot/2.1 (+https://www.google.com/bot.html)'])
        ->get('/contact')
        ->assertOk();

    expect(PageView::query()->first()->is_bot)->toBeTrue();
});

test('a browser visit is stored as a human', function () {
    $this->withoutDefer();

    $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36'])
        ->get('/contact')
        ->assertOk();

    expect(PageView::query()->first()->is_bot)->toBeFalse();
});

test('prefetch requests are not tracked', function () {
    $this->withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0 Safari/537.36',
        'Purpose' => 'prefetch',
    ])->get('/contact')->assertOk();

    expect(PageView::count())->toBe(0);
});

test('requests without a user agent are not tracked', function () {
    $this->withHeaders(['User-Agent' => ''])
        ->get('/contact')
        ->assertOk();

    expect(PageView::count())->toBe(0);
});
