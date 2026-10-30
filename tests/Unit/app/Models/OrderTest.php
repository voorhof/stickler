<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Observers\OrderObserver;
use App\Policies\OrderPolicy;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Support\LogOptions;

test('an order can be created', function () {
    $user = User::factory()->create();

    $order = Order::forceCreate([
        'order_number' => 'ORD-2026-00100',
        'user_id' => $user->id,
        'status' => OrderStatus::Pending,
        'payment_status' => PaymentStatus::Unpaid,
        'subtotal' => 10000,
        'tax_amount' => 2100,
        'shipping_amount' => 599,
        'total' => 12699,
        'shipping_address' => ['street' => 'Teststraat 1', 'city' => 'Amsterdam', 'postal_code' => '1012AB', 'country' => 'NL'],
        'billing_address' => ['street' => 'Teststraat 1', 'city' => 'Amsterdam', 'postal_code' => '1012AB', 'country' => 'NL'],
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($order->order_number)->toBe('ORD-2026-00100')
        ->and($order->status)->toBe(OrderStatus::Pending)
        ->and($order->payment_status)->toBe(PaymentStatus::Unpaid);
});

test('an order can be mass assigned fillable values', function () {
    $order = new Order([
        'order_number' => 'ORD-2026-00101',
        'user_id' => 1,
        'status' => OrderStatus::Pending,
        'payment_status' => PaymentStatus::Unpaid,
        'subtotal' => 5000,
        'tax_amount' => 1050,
        'shipping_amount' => 299,
        'total' => 6349,
        'shipping_address' => ['street' => 'Main St 10', 'city' => 'Rotterdam', 'postal_code' => '3011AA', 'country' => 'NL'],
        'billing_address' => ['street' => 'Main St 10', 'city' => 'Rotterdam', 'postal_code' => '3011AA', 'country' => 'NL'],
        'payment_method' => 'credit_card',
        'notes' => 'Please leave at the door',
    ]);

    expect($order->order_number)->toBe('ORD-2026-00101')
        ->and($order->status)->toBe(OrderStatus::Pending)
        ->and($order->payment_status)->toBe(PaymentStatus::Unpaid)
        ->and($order->subtotal)->toBe(5000)
        ->and($order->tax_amount)->toBe(1050)
        ->and($order->shipping_amount)->toBe(299)
        ->and($order->total)->toBe(6349)
        ->and($order->payment_method)->toBe('credit_card')
        ->and($order->notes)->toBe('Please leave at the door');
});

test('order number is auto generated on creation', function () {
    $order = Order::factory()->create(['order_number' => null]);

    expect($order->order_number)->toMatch('/^ORD-\d{4}-\d{5}$/');
});

test('order number is unique', function () {
    $order1 = Order::factory()->create();
    $order2 = Order::factory()->create();

    expect($order1->order_number)->not->toBe($order2->order_number);
});

test('order number is unique within the same year', function () {
    $order1 = Order::factory()->create();
    $order2 = Order::factory()->create();
    $order3 = Order::factory()->create();

    expect([$order1->order_number, $order2->order_number, $order3->order_number])
        ->toHaveCount(3)
        ->and(collect([$order1->order_number, $order2->order_number, $order3->order_number])->unique())->toHaveCount(3);
});

test('order belongs to user', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    expect($order->user)->not->toBeNull()
        ->and($order->user->id)->toBe($user->id);
});

test('order user can be null for guest orders', function () {
    $order = Order::factory()->guest()->create();

    expect($order->user_id)->toBeNull()
        ->and($order->user)->toBeNull();
});

test('order has many items', function () {
    $order = Order::factory()->create();
    OrderItem::factory()->count(3)->create(['order_id' => $order->id]);

    expect($order->orderItems)->toHaveCount(3);
});

test('order items are deleted when order is deleted', function () {
    $order = Order::factory()->create();
    OrderItem::factory()->count(2)->create(['order_id' => $order->id]);

    $order->forceDelete();

    expect(OrderItem::withTrashed()->where('order_id', $order->id)->count())->toBe(0);
});

test('status is cast to order status enum', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Pending]);

    expect($order->status)->toBeInstanceOf(OrderStatus::class)
        ->and($order->status)->toBe(OrderStatus::Pending);
});

test('payment status is cast to payment status enum', function () {
    $order = Order::factory()->create(['payment_status' => PaymentStatus::Unpaid]);

    expect($order->payment_status)->toBeInstanceOf(PaymentStatus::class)
        ->and($order->payment_status)->toBe(PaymentStatus::Unpaid);
});

test('shipping address is cast to array', function () {
    $address = ['street' => 'Teststraat 1', 'city' => 'Amsterdam', 'postal_code' => '1012AB', 'country' => 'NL'];
    $order = Order::factory()->create(['shipping_address' => $address]);

    expect($order->shipping_address)->toBeArray()
        ->and($order->shipping_address['city'])->toBe('Amsterdam');
});

test('billing address is cast to array', function () {
    $address = ['street' => 'Billing Ave 5', 'city' => 'Utrecht', 'postal_code' => '3511AA', 'country' => 'NL'];
    $order = Order::factory()->create(['billing_address' => $address]);

    expect($order->billing_address)->toBeArray()
        ->and($order->billing_address['city'])->toBe('Utrecht');
});

test('subtotal accessor converts cents to euros', function () {
    DB::table('orders')->insert([
        'order_number' => 'ORD-2026-00099',
        'subtotal' => 1999,
        'updated_at' => now(),
        'created_at' => now(),
    ]);
    $order = Order::where('order_number', 'ORD-2026-00099')->first();

    expect($order->subtotal)->toBe(19.99);
});

test('subtotal mutator converts euros to cents', function () {
    $order = Order::factory()->create();
    $order->subtotal = 25.50;
    $order->save();

    expect($order->getAttributes()['subtotal'])->toBe(2550);
});

test('tax amount accessor converts cents to euros', function () {
    DB::table('orders')->insert([
        'order_number' => 'ORD-2026-00097',
        'tax_amount' => 420,
        'updated_at' => now(),
        'created_at' => now(),
    ]);
    $order = Order::where('order_number', 'ORD-2026-00097')->first();

    expect($order->tax_amount)->toBe(4.20);
});

test('tax amount mutator converts euros to cents', function () {
    $order = Order::factory()->create();
    $order->tax_amount = 12.34;
    $order->save();

    expect($order->getAttributes()['tax_amount'])->toBe(1234);
});

test('shipping amount accessor converts cents to euros', function () {
    DB::table('orders')->insert([
        'order_number' => 'ORD-2026-00096',
        'shipping_amount' => 599,
        'updated_at' => now(),
        'created_at' => now(),
    ]);
    $order = Order::where('order_number', 'ORD-2026-00096')->first();

    expect($order->shipping_amount)->toBe(5.99);
});

test('shipping amount mutator converts euros to cents', function () {
    $order = Order::factory()->create();
    $order->shipping_amount = 9.99;
    $order->save();

    expect($order->getAttributes()['shipping_amount'])->toBe(999);
});

test('total accessor converts cents to euros', function () {
    DB::table('orders')->insert([
        'order_number' => 'ORD-2026-00095',
        'total' => 2478,
        'updated_at' => now(),
        'created_at' => now(),
    ]);
    $order = Order::where('order_number', 'ORD-2026-00095')->first();

    expect($order->total)->toBe(24.78);
});

test('total mutator converts euros to cents', function () {
    $order = Order::factory()->create();
    $order->total = 49.99;
    $order->save();

    expect($order->getAttributes()['total'])->toBe(4999);
});

test('customer name returns user name when user exists', function () {
    $user = User::factory()->create(['name' => 'John Doe']);
    $order = Order::factory()->create(['user_id' => $user->id]);

    expect($order->customerName())->toBe('John Doe');
});

test('customer name returns guest when user is null', function () {
    $order = Order::factory()->guest()->create();

    expect($order->customerName())->toBe(__('Guest'));
});

test('factory confirmed state sets confirmed status', function () {
    $order = Order::factory()->confirmed()->create();

    expect($order->status)->toBe(OrderStatus::Confirmed);
});

test('factory processing state sets processing status', function () {
    $order = Order::factory()->processing()->create();

    expect($order->status)->toBe(OrderStatus::Processing);
});

test('factory shipped state sets shipped status', function () {
    $order = Order::factory()->shipped()->create();

    expect($order->status)->toBe(OrderStatus::Shipped);
});

test('factory delivered state sets delivered status', function () {
    $order = Order::factory()->delivered()->create();

    expect($order->status)->toBe(OrderStatus::Delivered);
});

test('factory cancelled state sets cancelled status', function () {
    $order = Order::factory()->cancelled()->create();

    expect($order->status)->toBe(OrderStatus::Cancelled);
});

test('factory paid state sets paid payment status', function () {
    $order = Order::factory()->paid()->create();

    expect($order->payment_status)->toBe(PaymentStatus::Paid)
        ->and($order->payment_method)->not->toBeNull();
});

test('factory refunded state sets refunded payment status', function () {
    $order = Order::factory()->refunded()->create();

    expect($order->payment_status)->toBe(PaymentStatus::Refunded);
});

test('factory guest state sets null user', function () {
    $order = Order::factory()->guest()->create();

    expect($order->user_id)->toBeNull();
});

test('order belongs to a creator user', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['created_by_user_id' => $user->id]);

    expect($order->creator)->toBeInstanceOf(User::class)
        ->and($order->creator->id)->toBe($user->id);
});

test('order belongs to an updater user', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['updated_by_user_id' => $user->id]);

    expect($order->updater)->toBeInstanceOf(User::class)
        ->and($order->updater->id)->toBe($user->id);
});

test('it returns a default user instance when creator is not loaded', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create();

    $user->forceDelete();

    expect($order->creator)->toBeInstanceOf(User::class)
        ->and($order->creator->name)->toBe('Guest User');
});

test('it returns a default user instance when updater is not loaded', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create();

    $user->forceDelete();

    expect($order->updater)->toBeInstanceOf(User::class)
        ->and($order->updater->name)->toBe('Guest User');
});

test('it configures activity log options correctly', function () {
    $order = new Order;
    $options = $order->getActivitylogOptions();

    expect($options)->toBeInstanceOf(LogOptions::class);
});

test('payment method is nullable', function () {
    $order = Order::factory()->create(['payment_method' => null]);

    expect($order->payment_method)->toBeNull();
});

test('notes is nullable', function () {
    $order = Order::factory()->create(['notes' => null]);

    expect($order->notes)->toBeNull();
});

test('notes can be stored and retrieved', function () {
    $order = Order::factory()->create(['notes' => 'Please ring the doorbell']);

    expect($order->notes)->toBe('Please ring the doorbell');
});

test('order model has order policy attribute configured', function () {
    $attributes = new ReflectionClass(Order::class)->getAttributes(UsePolicy::class);

    expect($attributes)->toHaveCount(1)
        ->and($attributes[0]->getArguments()[0])->toBe(OrderPolicy::class);
});

test('order model has observer attribute configured', function () {
    $attributes = new ReflectionClass(Order::class)->getAttributes(ObservedBy::class);

    expect($attributes)->toHaveCount(1)
        ->and($attributes[0]->getArguments()[0])->toBe(OrderObserver::class);
});
