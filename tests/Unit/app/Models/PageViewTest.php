<?php

use App\Models\PageView;

test('a page view can be created with the factory', function () {
    $pageView = PageView::factory()->create([
        'path' => '/contact',
        'route_name' => 'contact.index',
        'visitor_hash' => hash('sha256', 'visitor'),
        'viewed_on' => now()->toDateString(),
    ]);

    expect($pageView->path)->toBe('/contact')
        ->and($pageView->route_name)->toBe('contact.index')
        ->and($pageView->visitor_hash)->toBe(hash('sha256', 'visitor'))
        ->and($pageView->viewed_on)->toBe(now()->toDateString());
});

test('a page view can be mass assigned fillable values', function () {
    $pageView = new PageView([
        'path' => '/terms',
        'route_name' => 'terms.index',
        'referrer_host' => 'example.com',
        'visitor_hash' => hash('sha256', 'visitor'),
        'viewed_on' => '2024-01-15',
    ]);

    expect($pageView->path)->toBe('/terms')
        ->and($pageView->route_name)->toBe('terms.index')
        ->and($pageView->referrer_host)->toBe('example.com')
        ->and($pageView->visitor_hash)->toBe(hash('sha256', 'visitor'))
        ->and($pageView->viewed_on)->toBe('2024-01-15');
});

test('a page view can be mass assigned the is bot flag', function () {
    $pageView = new PageView([
        'path' => '/contact',
        'is_bot' => true,
    ]);

    expect($pageView->is_bot)->toBeTrue();
});

test('is bot is cast to a boolean', function () {
    $pageView = PageView::factory()->bot()->create();

    expect($pageView->fresh()->is_bot)->toBeBool()
        ->and($pageView->fresh()->is_bot)->toBeTrue();
});

test('is bot defaults to a human visit when cast from the database', function () {
    $pageView = PageView::factory()->create(['is_bot' => 0]);

    expect($pageView->fresh()->is_bot)->toBeFalse();
});

test('route name and referrer host are nullable', function () {
    $pageView = PageView::factory()->create([
        'route_name' => null,
        'referrer_host' => null,
    ]);

    expect($pageView->route_name)->toBeNull()
        ->and($pageView->referrer_host)->toBeNull();
});

test('it uses the page views table', function () {
    expect((new PageView)->getTable())->toBe('page_views');
});

test('it has no updated at column', function () {
    expect((new PageView)->getUpdatedAtColumn())->toBeNull();
});

test('it sets created at automatically when created', function () {
    $pageView = PageView::factory()->create();

    expect($pageView->fresh()->created_at)->not->toBeNull();
});
