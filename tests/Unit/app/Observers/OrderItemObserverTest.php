<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('tracks creator and updater on order item create and update', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $order = Order::factory()->create([
        'user_id' => $creator->id,
        'created_by_user_id' => $creator->id,
        'updated_by_user_id' => $creator->id,
    ]);
    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'created_by_user_id' => $creator->id,
        'updated_by_user_id' => $creator->id,
    ]);

    expect($orderItem->created_by_user_id)->toBe($creator->id)
        ->and($orderItem->updated_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $orderItem->update(['quantity' => 5]);

    expect($orderItem->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($orderItem->fresh()->updated_by_user_id)->toBe($updater->id);
});
