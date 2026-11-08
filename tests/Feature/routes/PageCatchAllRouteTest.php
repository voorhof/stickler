<?php

use App\Models\Page;

test('it matches valid published pages on root catch-all route', function () {
    $page = Page::create([
        'title' => 'About Our Mission',
        'intro' => 'Mission intro',
        'published_at' => now(),
    ]);

    $response = $this->get('/'.$page->slug);

    $response->assertOk();
    $response->assertSee('About Our Mission');
});

test('it matches pages with numeric suffixed slugs on root catch-all route', function () {
    $page = Page::create([
        'title' => 'Cart',
        'intro' => 'Cart description page',
        'published_at' => now(),
    ]);

    expect($page->slug)->toBe('cart-1');

    $response = $this->get('/cart-1');

    $response->assertOk();
    $response->assertSee('Cart description page');
});

test('it does not match system reserved routes via PageController', function () {
    // /cart routes to CartController, not PageController
    $cartResponse = $this->get('/cart');
    $cartResponse->assertOk();
    $cartResponse->assertViewIs('public.cart.index');

    // /contact routes to ContactController, not PageController
    $contactResponse = $this->get('/contact');
    $contactResponse->assertOk();
    $contactResponse->assertViewIs('public.contact');

    // /brandbook routes to BrandbookController
    $brandbookResponse = $this->get('/brandbook');
    $brandbookResponse->assertOk();
    $brandbookResponse->assertViewIs('public.brandbook');
});

test('it prevents reserved excluded slug values from matching page route when no system route exists', function () {
    // 'dashboard' or 'wachtwoord-reset' are in excluded_from_slug
    $response = $this->get('/email-verificatie');

    // Should return 404 because negative lookahead blocks PageController from catching it
    $response->assertNotFound();
});
