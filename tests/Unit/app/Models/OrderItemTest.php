<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Observers\OrderItemObserver;
use App\Policies\OrderItemPolicy;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Support\LogOptions;

test('an order item can be created', function () {
    $user = User::factory()->create();

    $order = Order::forceCreate([
        'order_number' => 'ORD-2026-00200',
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'subtotal' => 10000,
        'tax_amount' => 2100,
        'shipping_amount' => 599,
        'total' => 12699,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    $orderItem = OrderItem::forceCreate([
        'order_id' => $order->id,
        'product_id' => null,
        'product_variant_id' => null,
        'product_name' => 'Test Product',
        'product_variant_label' => null,
        'sku' => 'SKU-TEST-001',
        'quantity' => 2,
        'unit_price' => 2999,
        'total_price' => 5998,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($orderItem->product_name)->toBe('Test Product')
        ->and($orderItem->quantity)->toBe(2)
        ->and($orderItem->sku)->toBe('SKU-TEST-001');
});

test('an order item can be mass assigned fillable values', function () {
    $orderItem = new OrderItem([
        'order_id' => 1,
        'product_id' => 1,
        'product_variant_id' => 1,
        'product_name' => 'Mass Assigned Product',
        'product_variant_label' => 'Red / Large',
        'sku' => 'SKU-MASS-001',
        'quantity' => 3,
        'unit_price' => 1999,
        'total_price' => 5997,
    ]);

    expect($orderItem->product_name)->toBe('Mass Assigned Product')
        ->and($orderItem->product_variant_label)->toBe('Red / Large')
        ->and($orderItem->sku)->toBe('SKU-MASS-001')
        ->and($orderItem->quantity)->toBe(3)
        ->and($orderItem->unit_price)->toBe(1999)
        ->and($orderItem->total_price)->toBe(5997);
});

test('order item belongs to order', function () {
    $order = Order::factory()->create();
    $orderItem = OrderItem::factory()->create(['order_id' => $order->id]);

    expect($orderItem->order)->not->toBeNull()
        ->and($orderItem->order->id)->toBe($order->id);
});

test('order item belongs to product', function () {
    User::factory()->create();
    $product = Product::factory()->create();
    $order = Order::factory()->create();
    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
    ]);

    expect($orderItem->product)->not->toBeNull()
        ->and($orderItem->product->id)->toBe($product->id);
});

test('order item product can be null if product was deleted', function () {
    $order = Order::factory()->create();
    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => null,
    ]);

    expect($orderItem->product)->toBeNull();
});

test('order item belongs to product variant', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);
    $order = Order::factory()->create();
    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_variant_id' => $variant->id,
    ]);

    expect($orderItem->productVariant)->not->toBeNull()
        ->and($orderItem->productVariant->id)->toBe($variant->id);
});

test('order item product variant can be null', function () {
    $order = Order::factory()->create();
    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_variant_id' => null,
    ]);

    expect($orderItem->productVariant)->toBeNull();
});

test('unit price accessor converts cents to euros', function () {
    $order = Order::factory()->create();
    DB::table('order_items')->insert([
        'order_id' => $order->id,
        'product_name' => 'Test Product',
        'sku' => 'SKU-UNIT-001',
        'quantity' => 1,
        'unit_price' => 2999,
        'total_price' => 2999,
        'updated_at' => now(),
        'created_at' => now(),
    ]);
    $orderItem = OrderItem::where('sku', 'SKU-UNIT-001')->first();

    expect($orderItem->unit_price)->toBe(29.99);
});

test('unit price mutator converts euros to cents', function () {
    $order = Order::factory()->create();
    $orderItem = OrderItem::factory()->create(['order_id' => $order->id]);
    $orderItem->unit_price = 19.99;
    $orderItem->save();

    expect($orderItem->getAttributes()['unit_price'])->toBe(1999);
});

test('total price accessor converts cents to euros', function () {
    $order = Order::factory()->create();
    DB::table('order_items')->insert([
        'order_id' => $order->id,
        'product_name' => 'Test Product',
        'sku' => 'SKU-TOTAL-001',
        'quantity' => 2,
        'unit_price' => 1500,
        'total_price' => 3000,
        'updated_at' => now(),
        'created_at' => now(),
    ]);
    $orderItem = OrderItem::where('sku', 'SKU-TOTAL-001')->first();

    expect($orderItem->total_price)->toBe(30);
});

test('total price mutator converts euros to cents', function () {
    $order = Order::factory()->create();
    $orderItem = OrderItem::factory()->create(['order_id' => $order->id]);
    $orderItem->total_price = 45.50;
    $orderItem->save();

    expect($orderItem->getAttributes()['total_price'])->toBe(4550);
});

test('quantity is cast to integer', function () {
    $order = Order::factory()->create();
    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'quantity' => '5',
    ]);

    expect($orderItem->quantity)->toBe(5)->toBeInt();
});

test('product name is stored as a snapshot', function () {
    $order = Order::factory()->create();
    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_name' => 'Original Product Name',
    ]);

    expect($orderItem->product_name)->toBe('Original Product Name');
});

test('product variant label is nullable', function () {
    $order = Order::factory()->create();
    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_variant_label' => null,
    ]);

    expect($orderItem->product_variant_label)->toBeNull();
});

test('product variant label is stored as a snapshot', function () {
    $order = Order::factory()->create();
    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_variant_label' => 'Blue / Medium',
    ]);

    expect($orderItem->product_variant_label)->toBe('Blue / Medium');
});

test('sku is nullable', function () {
    $order = Order::factory()->create();
    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'sku' => null,
    ]);

    expect($orderItem->sku)->toBeNull();
});

test('factory with variant state sets variant fields', function () {
    $order = Order::factory()->create();
    $orderItem = OrderItem::factory()->withVariant()->create(['order_id' => $order->id]);

    expect($orderItem->product_variant_id)->not->toBeNull()
        ->and($orderItem->product_variant_label)->not->toBeNull();
});

test('order item belongs to a creator user', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create();
    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'created_by_user_id' => $user->id,
    ]);

    expect($orderItem->creator)->toBeInstanceOf(User::class)
        ->and($orderItem->creator->id)->toBe($user->id);
});

test('order item belongs to an updater user', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create();
    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($orderItem->updater)->toBeInstanceOf(User::class)
        ->and($orderItem->updater->id)->toBe($user->id);
});

test('it returns a default user instance when creator is not loaded', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create();
    $orderItem = OrderItem::factory()->create(['order_id' => $order->id]);

    $user->forceDelete();

    expect($orderItem->creator)->toBeInstanceOf(User::class)
        ->and($orderItem->creator->name)->toBe('Guest User');
});

test('it returns a default user instance when updater is not loaded', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create();
    $orderItem = OrderItem::factory()->create(['order_id' => $order->id]);

    $user->forceDelete();

    expect($orderItem->updater)->toBeInstanceOf(User::class)
        ->and($orderItem->updater->name)->toBe('Guest User');
});

test('it configures activity log options correctly', function () {
    $orderItem = new OrderItem;
    $options = $orderItem->getActivitylogOptions();

    expect($options)->toBeInstanceOf(LogOptions::class);
});

test('order item model has order item policy attribute configured', function () {
    $attributes = new ReflectionClass(OrderItem::class)->getAttributes(UsePolicy::class);

    expect($attributes)->toHaveCount(1)
        ->and($attributes[0]->getArguments()[0])->toBe(OrderItemPolicy::class);
});

test('order item model has observer attribute configured', function () {
    $attributes = new ReflectionClass(OrderItem::class)->getAttributes(ObservedBy::class);

    expect($attributes)->toHaveCount(1)
        ->and($attributes[0]->getArguments()[0])->toBe(OrderItemObserver::class);
});
