<?php

use App\Models\Order;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('tracks creator and updater on order create and update', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $order = Order::factory()->create();

    expect($order->created_by_user_id)->toBe($creator->id)
        ->and($order->updated_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $order->update(['notes' => 'Updated notes']);

    expect($order->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($order->fresh()->updated_by_user_id)->toBe($updater->id);
});
