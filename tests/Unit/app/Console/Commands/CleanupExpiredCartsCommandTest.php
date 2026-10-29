<?php

use App\Models\Cart;

it('deletes expired carts and keeps non-expired ones', function () {
    Cart::factory()->expired()->create();
    Cart::factory()->expired()->create();
    Cart::factory()->create();

    $this->artisan('carts:cleanup')
        ->expectsOutput('2 expired cart(s) deleted.')
        ->assertSuccessful();

    expect(Cart::count())->toBe(1);
});

it('returns zero when there are no expired carts', function () {
    Cart::factory()->count(3)->create();

    $this->artisan('carts:cleanup')
        ->expectsOutput('0 expired cart(s) deleted.')
        ->assertSuccessful();

    expect(Cart::count())->toBe(3);
});
