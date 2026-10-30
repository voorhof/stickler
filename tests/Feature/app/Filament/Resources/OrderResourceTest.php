<?php

/** @noinspection PhpUnhandledExceptionInspection, PhpPossiblePolymorphicInvocationInspection */

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Actions\HardDeleteAction;
use App\Filament\Actions\RestoreDeletedAction;
use App\Filament\Actions\SoftDeleteAction;
use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantOption;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'create orders',
        'view orders',
        'update orders',
        'delete orders',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('can list orders', function () {
    $orders = Order::factory()->count(3)->create();

    livewire(ListOrders::class)
        ->assertOk()
        ->assertCanSeeTableRecords($orders)
        ->assertCountTableRecords(3);
});

it('can search orders by order number', function () {
    $order = Order::factory()->create();
    $otherOrder = Order::factory()->create();

    livewire(ListOrders::class)
        ->searchTable($order->order_number)
        ->assertCanSeeTableRecords([$order])
        ->assertCanNotSeeTableRecords([$otherOrder]);
});

it('can filter orders by status', function () {
    $pendingOrder = Order::factory()->create(['status' => OrderStatus::Pending]);
    $deliveredOrder = Order::factory()->create(['status' => OrderStatus::Delivered]);

    livewire(ListOrders::class)
        ->filterTable('status', 'pending')
        ->assertCanSeeTableRecords([$pendingOrder])
        ->assertCanNotSeeTableRecords([$deliveredOrder]);
});

it('can filter orders by payment status', function () {
    $paidOrder = Order::factory()->paid()->create();
    $unpaidOrder = Order::factory()->create();

    livewire(ListOrders::class)
        ->filterTable('payment_status', 'paid')
        ->assertCanSeeTableRecords([$paidOrder])
        ->assertCanNotSeeTableRecords([$unpaidOrder]);
});

it('can filter orders by trashed state', function () {
    $activeOrder = Order::factory()->create();
    $trashedOrder = Order::factory()->softDeleted()->create();

    livewire(ListOrders::class)
        ->filterTable('trashed', false)
        ->assertCanSeeTableRecords([$trashedOrder])
        ->assertCanNotSeeTableRecords([$activeOrder])
        ->filterTable('trashed', true)
        ->assertCanSeeTableRecords([$activeOrder, $trashedOrder]);
});

it('validates required fields when creating an order', function () {
    livewire(CreateOrder::class)
        ->fillForm([
            'status' => null,
            'payment_status' => null,
            'shipping_amount' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'status' => 'required',
            'payment_status' => 'required',
            'shipping_amount' => 'required',
        ])
        ->assertNotNotified();
});

it('can create an order', function () {
    $user = User::factory()->create();

    livewire(CreateOrder::class)
        ->fillForm([
            'user_id' => $user->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => 100.00,
            'tax_amount' => 21.00,
            'shipping_amount' => 10.00,
            'total' => 131.00,
            'payment_method' => 'bank_transfer',
            'notes' => 'Customer requested gift wrapping.',
            'items' => [],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $order = Order::where('payment_method', 'bank_transfer')->first();
    expect($order)->not->toBeNull()
        ->and($order->status)->toBe(OrderStatus::Pending)
        ->and($order->payment_status)->toBe(PaymentStatus::Unpaid)
        ->and($order->user_id)->toBe($user->id)
        ->and($order->notes)->toBe('Customer requested gift wrapping.');
});

it('can create an order with items', function () {
    $product = Product::factory()->create();

    livewire(CreateOrder::class)
        ->fillForm([
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => 59.98,
            'tax_amount' => 12.59,
            'shipping_amount' => 5.00,
            'total' => 77.57,
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_name' => $product->title,
                    'sku' => $product->sku,
                    'quantity' => 2,
                    'unit_price' => 29.99,
                    'total_price' => 59.98,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $order = Order::latest()->first();
    expect($order->items)->toHaveCount(1);

    $item = $order->items->first();
    expect($item->product_id)->toBe($product->id)
        ->and($item->product_name)->toBe($product->title)
        ->and($item->quantity)->toBe(2);
});

it('calculates order prices from items correctly', function () {
    $product = Product::factory()->create();

    livewire(CreateOrder::class)
        ->fillForm([
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => 59.98,
            'tax_amount' => 12.59,
            'shipping_amount' => 5.00,
            'total' => 77.57,
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_name' => $product->title,
                    'sku' => $product->sku,
                    'quantity' => 2,
                    'unit_price' => 29.99,
                    'total_price' => 59.98,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $order = Order::latest()->first();

    // Subtotal = sum of (unit_price * quantity) for all items = 29.99 * 2 = 59.98 EUR
    expect($order->subtotal)->toBe(59.98)
        ->and($order->tax_amount)->toBe(12.60)
        ->and($order->total)->toBe(77.58);

    // Tax = 21% of subtotal = 59.98 * 0.21 = 12.5958, rounded to 12.60 EUR

    // Total = subtotal + tax + shipping = 59.98 + 12.60 + 5.00 = 77.58 EUR
});

it('rounds calculated price fields to two decimals', function () {
    $product = Product::factory()->create();

    livewire(CreateOrder::class)
        ->fillForm([
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_name' => $product->title,
                    'sku' => $product->sku,
                    'quantity' => 3,
                    'unit_price' => 0.1,
                ],
            ],
        ])
        ->assertSchemaStateSet([
            'subtotal' => 0.3,
            'tax_amount' => 0.06,
            'total' => 0.36,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $order = Order::latest()->first();

    // 0.1 * 3 = 0.30000000000000004 in floating point, so the calculated values must be rounded to 2 decimals
    expect($order->subtotal)->toBe(0.3)
        ->and($order->tax_amount)->toBe(0.06)
        ->and($order->total)->toBe(0.36)
        ->and($order->items->first()->total_price)->toBe(0.3);
});

it('calculates order prices with multiple items', function () {
    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();

    livewire(CreateOrder::class)
        ->fillForm([
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => 109.97,
            'tax_amount' => 23.09,
            'shipping_amount' => 0,
            'total' => 133.06,
            'items' => [
                [
                    'product_id' => $product1->id,
                    'product_name' => $product1->title,
                    'sku' => $product1->sku,
                    'quantity' => 1,
                    'unit_price' => 29.99,
                    'total_price' => 29.99,
                ],
                [
                    'product_id' => $product2->id,
                    'product_name' => $product2->title,
                    'sku' => $product2->sku,
                    'quantity' => 3,
                    'unit_price' => 26.66,
                    'total_price' => 79.98,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $order = Order::latest()->first();
    expect($order->items)->toHaveCount(2)
        ->and($order->subtotal)->toBe(109.97)
        ->and($order->tax_amount)->toBe(23.09)
        ->and($order->total)->toBe(133.06);

    // Subtotal = 29.99 * 1 + 26.66 * 3 = 29.99 + 79.98 = 109.97 EUR

    // Tax = 21% of 109.97 = 23.0937, rounded to 23.09 EUR

    // Total = 109.97 + 23.09 + 0 = 133.06 EUR
});

it('can create an order with product variant', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
    $option = ProductVariantOption::factory()->create();
    $variant->options()->attach($option);

    livewire(CreateOrder::class)
        ->fillForm([
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => 29.99,
            'tax_amount' => 6.30,
            'shipping_amount' => 5.00,
            'total' => 41.29,
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_name' => $product->title,
                    'product_variant_id' => $variant->id,
                    'product_variant_label' => $variant->label,
                    'sku' => $product->sku,
                    'quantity' => 1,
                    'unit_price' => 29.99,
                    'total_price' => 29.99,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $order = Order::latest()->first();
    expect($order->items)->toHaveCount(1);

    $item = $order->items->first();
    expect($item->product_id)->toBe($product->id)
        ->and($item->product_variant_id)->toBe($variant->id)
        ->and($item->product_variant_label)->toBe($variant->label);
});

it('validates order item required fields', function () {
    livewire(CreateOrder::class)
        ->fillForm([
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => 100.00,
            'tax_amount' => 21.00,
            'shipping_amount' => 10.00,
            'total' => 131.00,
            'items' => [
                [
                    'product_id' => null,
                    'quantity' => null,
                    'unit_price' => null,
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors([
            'items.0.product_id' => 'required',
            'items.0.quantity' => 'required',
            'items.0.unit_price' => 'required',
        ])
        ->assertNotNotified();
});

it('can create a guest order without a user', function () {
    livewire(CreateOrder::class)
        ->fillForm([
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => 50.00,
            'tax_amount' => 10.50,
            'shipping_amount' => 5.00,
            'total' => 65.50,
            'items' => [],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $order = Order::latest()->first();
    expect($order->user_id)->toBeNull()
        ->and($order->customerName())->toBe(__('Guest'));
});

it('can create an order with shipping and billing addresses', function () {
    livewire(CreateOrder::class)
        ->fillForm([
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => 75.00,
            'tax_amount' => 15.75,
            'shipping_amount' => 8.00,
            'total' => 98.75,
            'shipping_address' => [
                'street' => 'Herengracht 123',
                'city' => 'Amsterdam',
                'postal_code' => '1015 BG',
                'country' => 'Netherlands',
            ],
            'billing_address' => [
                'street' => 'Kalverstraat 45',
                'city' => 'Amsterdam',
                'postal_code' => '1012 PH',
                'country' => 'Netherlands',
            ],
            'items' => [],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $order = Order::latest()->first();
    expect($order->shipping_address['city'])->toBe('Amsterdam')
        ->and($order->billing_address['city'])->toBe('Amsterdam');
});

it('validates required fields when editing an order', function () {
    $order = Order::factory()->create();

    livewire(EditOrder::class, ['record' => $order->getRouteKey()])
        ->fillForm([
            'status' => null,
            'payment_status' => null,
            'shipping_amount' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'status' => 'required',
            'payment_status' => 'required',
            'shipping_amount' => 'required',
        ])
        ->assertNotNotified();
});

it('can edit an order', function () {
    $order = Order::factory()->create([
        'status' => OrderStatus::Pending,
        'payment_status' => PaymentStatus::Unpaid,
    ]);
    $user = User::factory()->create();

    livewire(EditOrder::class, ['record' => $order->getRouteKey()])
        ->fillForm([
            'user_id' => $user->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'subtotal' => 200.00,
            'tax_amount' => 42.00,
            'shipping_amount' => 15.00,
            'total' => 257.00,
            'notes' => 'Updated notes.',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Confirmed)
        ->and($order->payment_status)->toBe(PaymentStatus::Paid)
        ->and($order->user_id)->toBe($user->id)
        ->and($order->notes)->toBe('Updated notes.');
});

it('can edit order items', function () {
    $product = Product::factory()->create();
    $order = Order::factory()->create();
    OrderItem::factory()->count(2)->create(['order_id' => $order->id]);

    livewire(EditOrder::class, ['record' => $order->getRouteKey()])
        ->fillForm([
            'items' => [
                [
                    'id' => $order->items[0]->id,
                    'product_id' => $product->id,
                    'product_name' => $product->title,
                    'quantity' => 5,
                    'unit_price' => 49.99,
                    'total_price' => 249.95,
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $order->refresh();
    expect($order->items)->toHaveCount(1);

    $item = $order->items->first();
    expect($item->product_id)->toBe($product->id)
        ->and($item->product_name)->toBe($product->title)
        ->and($item->quantity)->toBe(5);
});

it('can soft delete an order', function () {
    $order = Order::factory()->create();

    livewire(EditOrder::class, ['record' => $order->getRouteKey()])
        ->callAction(SoftDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertSoftDeleted(Order::class, ['id' => $order->id]);
});

it('can force delete a soft-deleted order', function () {
    $order = Order::factory()->softDeleted()->create();

    livewire(EditOrder::class, ['record' => $order->getRouteKey()])
        ->callAction(HardDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertDatabaseMissing(Order::class, ['id' => $order->id]);
});

it('can restore a soft-deleted order', function () {
    $order = Order::factory()->softDeleted()->create();

    livewire(EditOrder::class, ['record' => $order->getRouteKey()])
        ->callAction(RestoreDeletedAction::class)
        ->assertNotified();

    $this->assertNotSoftDeleted(Order::class, ['id' => $order->id]);
});

it('can bulk delete orders', function () {
    $orders = Order::factory()->count(2)->create();

    livewire(ListOrders::class)
        ->callTableBulkAction('softDeleteBulk', $orders)
        ->assertNotified();

    foreach ($orders as $order) {
        $this->assertSoftDeleted(Order::class, ['id' => $order->id]);
    }
});

it('can bulk force delete soft-deleted orders', function () {
    $orders = Order::factory()->count(2)->softDeleted()->create();

    livewire(ListOrders::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('hardDeleteBulk', $orders)
        ->assertNotified();

    foreach ($orders as $order) {
        $this->assertDatabaseMissing(Order::class, ['id' => $order->id]);
    }
});

it('can bulk restore soft-deleted orders', function () {
    $orders = Order::factory()->count(2)->softDeleted()->create();

    livewire(ListOrders::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('restoreDeletedBulk', $orders)
        ->assertNotified();

    foreach ($orders as $order) {
        $this->assertNotSoftDeleted(Order::class, ['id' => $order->id]);
    }
});

it('denies destructive bulk actions when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view orders']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $activeOrder = Order::factory()->create();
    $trashedOrder = Order::factory()->softDeleted()->create();

    livewire(ListOrders::class)
        ->assertTableBulkActionHidden('softDeleteBulk');

    livewire(ListOrders::class)
        ->filterTable('trashed', false)
        ->assertTableBulkActionHidden('restoreDeletedBulk')
        ->assertTableBulkActionHidden('hardDeleteBulk');

    $this->assertDatabaseHas(Order::class, ['id' => $activeOrder->id, 'deleted_at' => null]);
    $this->assertSoftDeleted(Order::class, ['id' => $trashedOrder->id]);
});

it('denies listing orders when the user does not have view permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(ListOrders::class)
        ->assertForbidden();
});

it('denies loading create order page when the user does not have create permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(CreateOrder::class)
        ->assertForbidden();
});

it('denies loading edit order page when the user does not have update permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view orders']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $order = Order::factory()->create();

    livewire(EditOrder::class, ['record' => $order->getRouteKey()])
        ->assertForbidden();
});

it('denies deleting an order when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view orders', 'update orders']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $order = Order::factory()->create();

    livewire(EditOrder::class, ['record' => $order->getRouteKey()])
        ->assertActionHidden(SoftDeleteAction::class);

    $this->assertDatabaseHas(Order::class, ['id' => $order->id]);
});
