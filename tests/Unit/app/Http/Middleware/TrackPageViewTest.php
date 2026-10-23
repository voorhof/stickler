<?php

use App\Models\PageView;

test('a public page view is stored without the raw ip address', function () {
    $this->get('/contact')->assertOk();

    $pageView = PageView::query()->first();

    expect(PageView::count())->toBe(1)
        ->and($pageView->path)->toBe('/contact')
        ->and($pageView->route_name)->toBe('contact.index')
        ->and($pageView->visitor_hash)->toHaveLength(64)
        ->and($pageView->visitor_hash)->not->toContain('127.0.0.1');
});

test('the visitor hash changes from one day to the next', function () {
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
