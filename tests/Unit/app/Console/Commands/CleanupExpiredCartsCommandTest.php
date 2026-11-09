<?php

use App\Models\Cart;

it('deletes expired carts when force flag is provided', function () {
    Cart::factory()->expired()->create();
    Cart::factory()->expired()->create();
    Cart::factory()->create();

    $this->artisan('carts:cleanup --force')
        ->expectsOutput('2 expired cart(s) deleted.')
        ->assertSuccessful();

    expect(Cart::count())->toBe(1);
});

it('returns zero when there are no expired carts with force flag', function () {
    Cart::factory()->count(3)->create();

    $this->artisan('carts:cleanup --force')
        ->expectsOutput('0 expired cart(s) deleted.')
        ->assertSuccessful();

    expect(Cart::count())->toBe(3);
});

it('asks for confirmation when force flag is not provided', function () {
    Cart::factory()->expired()->create();

    $this->artisan('carts:cleanup')
        ->expectsQuestion('Are you sure you want to delete all expired carts?', true)
        ->expectsOutput('1 expired cart(s) deleted.')
        ->assertSuccessful();

    expect(Cart::count())->toBe(0);
});

it('cancels when user declines confirmation', function () {
    Cart::factory()->expired()->create();

    $this->artisan('carts:cleanup')
        ->expectsQuestion('Are you sure you want to delete all expired carts?', false)
        ->expectsOutput('Cart cleanup cancelled.')
        ->assertSuccessful();

    expect(Cart::count())->toBe(1);
});
