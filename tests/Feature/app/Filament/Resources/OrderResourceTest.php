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
            'subtotal' => null,
            'tax_amount' => null,
            'shipping_amount' => null,
            'total' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'status' => 'required',
            'payment_status' => 'required',
            'subtotal' => 'required',
            'tax_amount' => 'required',
            'shipping_amount' => 'required',
            'total' => 'required',
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
            'subtotal' => 29.99,
            'tax_amount' => 6.30,
            'shipping_amount' => 5.00,
            'total' => 41.29,
            'items' => [
                [
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
    expect($item->product_name)->toBe($product->title)
        ->and($item->quantity)->toBe(2);
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
                    'product_name' => null,
                    'quantity' => null,
                    'unit_price' => null,
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors([
            'items.0.product_name' => 'required',
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
            'subtotal' => null,
            'tax_amount' => null,
            'shipping_amount' => null,
            'total' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'status' => 'required',
            'payment_status' => 'required',
            'subtotal' => 'required',
            'tax_amount' => 'required',
            'shipping_amount' => 'required',
            'total' => 'required',
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
    $order = Order::factory()->create();
    OrderItem::factory()->count(2)->create(['order_id' => $order->id]);

    livewire(EditOrder::class, ['record' => $order->getRouteKey()])
        ->fillForm([
            'items' => [
                [
                    'id' => $order->items[0]->id,
                    'product_name' => 'Updated Product Name',
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
    expect($item->product_name)->toBe('Updated Product Name')
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
