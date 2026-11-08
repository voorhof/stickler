<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Observers\OrderObserver;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    User::factory()->create();
});

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

it('restores product and variant stock when order status transitions to cancelled', function (): void {
    $product = Product::factory()->create(['stock_quantity' => 5]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'stock_quantity' => 2,
    ]);

    $order = Order::factory()->create([
        'status' => OrderStatus::Pending,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_variant_id' => null,
        'quantity' => 3,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'quantity' => 4,
    ]);

    // Initial stock unchanged before cancellation
    expect($product->fresh()->stock_quantity)->toBe(5)
        ->and($variant->fresh()->stock_quantity)->toBe(2);

    // Cancel order
    $order->update(['status' => OrderStatus::Cancelled]);

    expect($product->fresh()->stock_quantity)->toBe(8)
        ->and($variant->fresh()->stock_quantity)->toBe(6);
});

it('is idempotent and does not restore stock repeatedly when updating an already cancelled order', function (): void {
    $product = Product::factory()->create(['stock_quantity' => 5]);

    $order = Order::factory()->create([
        'status' => OrderStatus::Pending,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_variant_id' => null,
        'quantity' => 3,
    ]);

    // First transition to Cancelled restores stock once
    $order->update(['status' => OrderStatus::Cancelled]);
    expect($product->fresh()->stock_quantity)->toBe(8);

    // Subsequent updates to the cancelled order should not restore stock again
    $order->update(['notes' => 'Some other note']);
    expect($product->fresh()->stock_quantity)->toBe(8);

    $order->update(['status' => OrderStatus::Cancelled]);
    expect($product->fresh()->stock_quantity)->toBe(8);
});

it('restores stock when order is created directly with cancelled status', function (): void {
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $order = Order::factory()->create([
        'status' => OrderStatus::Cancelled,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_variant_id' => null,
        'quantity' => 5,
    ]);

    // If order was created directly as Cancelled and items loaded, calling restoreStockForOrder restores
    $orderObserver = new OrderObserver;
    $orderObserver->created($order);

    expect($product->fresh()->stock_quantity)->toBe(15);
});

it('does not restore stock for non-cancelled status transitions', function (): void {
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $order = Order::factory()->create([
        'status' => OrderStatus::Pending,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_variant_id' => null,
        'quantity' => 2,
    ]);

    $order->update(['status' => OrderStatus::Confirmed]);
    expect($product->fresh()->stock_quantity)->toBe(10);

    $order->update(['status' => OrderStatus::Processing]);
    expect($product->fresh()->stock_quantity)->toBe(10);

    $order->update(['status' => OrderStatus::Shipped]);
    expect($product->fresh()->stock_quantity)->toBe(10);

    $order->update(['status' => OrderStatus::Delivered]);
    expect($product->fresh()->stock_quantity)->toBe(10);
});
