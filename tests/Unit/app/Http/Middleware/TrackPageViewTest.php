<?php

use App\Models\PageView;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

test('crawler detect is registered as a singleton in the service container', function () {
    $instance1 = app(CrawlerDetect::class);
    $instance2 = app(CrawlerDetect::class);

    expect($instance1)->toBeInstanceOf(CrawlerDetect::class)
        ->and($instance1)->toBe($instance2);
});

test('it tracks page views on matching GET routes', function () {
    $this->withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
    ])->get(route('welcome.index'));

    expect(PageView::where('route_name', 'welcome.index')->where('is_bot', false)->exists())->toBeTrue();
});

test('it flags crawler bots using the injected crawler detect instance', function () {
    $this->withHeaders([
        'User-Agent' => 'Googlebot/2.1 (+https://www.google.com/bot.html)',
    ])->get(route('posts.index'));

    expect(PageView::where('route_name', 'posts.index')->where('is_bot', true)->exists())->toBeTrue();
});

test('it skips tracking for prefetch requests', function () {
    $this->withHeaders([
        'User-Agent' => 'Mozilla/5.0',
        'Purpose' => 'prefetch',
    ])->get(route('welcome.index'));

    expect(PageView::where('route_name', 'welcome.index')->exists())->toBeFalse();
});

test('it skips tracking when user agent is empty or response is not 200', function () {
    $this->withHeaders(['User-Agent' => ''])->get(route('welcome.index'));

    expect(PageView::where('route_name', 'welcome.index')->exists())->toBeFalse();
});
