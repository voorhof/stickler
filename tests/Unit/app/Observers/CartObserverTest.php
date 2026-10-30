<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

use App\Models\Cart;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('tracks creator and updater on cart create and update', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $cart = Cart::factory()->create();

    expect($cart->created_by_user_id)->toBe($creator->id)
        ->and($cart->updated_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $cart->update(['expires_at' => now()->addDays(60)]);

    expect($cart->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($cart->fresh()->updated_by_user_id)->toBe($updater->id);
});
