<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

use App\Models\CartItem;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('tracks creator and updater on cart item create and update', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $item = CartItem::factory()->create();

    expect($item->created_by_user_id)->toBe($creator->id)
        ->and($item->updated_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $item->update(['quantity' => 5]);

    expect($item->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($item->fresh()->updated_by_user_id)->toBe($updater->id);
});
