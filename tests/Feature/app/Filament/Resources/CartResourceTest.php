<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Resources\Carts\Pages\CreateCart;
use App\Filament\Resources\Carts\Pages\EditCart;
use App\Filament\Resources\Carts\Pages\ListCarts;
use App\Filament\Resources\Carts\Relations\CartItemsRelationManager;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantOption;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'create carts',
        'view carts',
        'update carts',
        'delete carts',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('can list carts', function () {
    $carts = Cart::factory()->count(3)->create();

    livewire(ListCarts::class)
        ->assertOk()
        ->assertCanSeeTableRecords($carts)
        ->assertCountTableRecords(3);
});

it('can search carts by cart token', function () {
    $cart = Cart::factory()->create();
    $otherCart = Cart::factory()->create();

    livewire(ListCarts::class)
        ->searchTable($cart->cart_token)
        ->assertCanSeeTableRecords([$cart])
        ->assertCanNotSeeTableRecords([$otherCart]);
});

it('can filter carts by guest status', function () {
    $guestCart = Cart::factory()->guest()->create();
    $userCart = Cart::factory()->forUser()->create();

    livewire(ListCarts::class)
        ->filterTable('is_guest', true)
        ->assertCanSeeTableRecords([$guestCart])
        ->assertCanNotSeeTableRecords([$userCart]);
});

it('can filter carts by customer', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $cart1 = Cart::factory()->create(['user_id' => $user1->id, 'is_guest' => false]);
    $cart2 = Cart::factory()->create(['user_id' => $user2->id, 'is_guest' => false]);

    livewire(ListCarts::class)
        ->filterTable('user_id', $user1->id)
        ->assertCanSeeTableRecords([$cart1])
        ->assertCanNotSeeTableRecords([$cart2]);
});

it('can create a cart', function () {
    livewire(CreateCart::class)
        ->fillForm()
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    expect(Cart::latest()->first())->not->toBeNull();
});

it('can create a cart for a customer', function () {
    $user = User::factory()->create();

    livewire(CreateCart::class)
        ->fillForm([
            'user_id' => $user->id,
            'is_guest' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $cart = Cart::latest()->first();
    expect($cart->user_id)->toBe($user->id)
        ->and($cart->is_guest)->toBeFalse();
});

it('can create a guest cart', function () {
    livewire(CreateCart::class)
        ->fillForm([
            'is_guest' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $cart = Cart::latest()->first();
    expect($cart->is_guest)->toBeTrue()
        ->and($cart->user_id)->toBeNull();
});

it('can create a cart with expiration date', function () {
    $expiresAt = now()->addDays(30);

    livewire(CreateCart::class)
        ->fillForm([
            'expires_at' => $expiresAt->format('Y-m-d'),
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $cart = Cart::latest()->first();
    expect($cart->expires_at->toDateString())->toBe($expiresAt->toDateString());
});

it('can edit a cart', function () {
    $cart = Cart::factory()->create();
    $user = User::factory()->create();

    livewire(EditCart::class, ['record' => $cart->getRouteKey()])
        ->fillForm([
            'user_id' => $user->id,
            'is_guest' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $cart->refresh();
    expect($cart->user_id)->toBe($user->id)
        ->and($cart->is_guest)->toBeFalse();
});

it('can edit cart expiration date', function () {
    $cart = Cart::factory()->create();
    $newExpiresAt = now()->addDays(60);

    livewire(EditCart::class, ['record' => $cart->getRouteKey()])
        ->fillForm([
            'expires_at' => $newExpiresAt->format('Y-m-d'),
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $cart->refresh();
    expect($cart->expires_at->toDateString())->toBe($newExpiresAt->toDateString());
});

it('can edit cart customer data', function () {
    $cart = Cart::factory()->create();

    livewire(EditCart::class, ['record' => $cart->getRouteKey()])
        ->fillForm([
            'customer_data.name' => 'John Doe',
            'customer_data.email' => 'john@example.com',
            'customer_data.phone' => '+32470123456',
            'customer_data.shipping_address.street' => 'Main St 1',
            'customer_data.shipping_address.city' => 'Brussels',
            'customer_data.shipping_address.postal_code' => '1000',
            'customer_data.shipping_address.country' => 'Belgium',
            'customer_data.billing_address.street' => 'Billing St 2',
            'customer_data.billing_address.city' => 'Antwerp',
            'customer_data.billing_address.postal_code' => '2000',
            'customer_data.billing_address.country' => 'Belgium',
            'customer_data.notes' => 'Please leave at the door',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $cart->refresh();
    $customerData = $cart->customer_data;
    expect($customerData['name'])->toBe('John Doe')
        ->and($customerData['email'])->toBe('john@example.com')
        ->and($customerData['phone'])->toBe('+32470123456')
        ->and($customerData['shipping_address']['street'])->toBe('Main St 1')
        ->and($customerData['shipping_address']['city'])->toBe('Brussels')
        ->and($customerData['shipping_address']['postal_code'])->toBe('1000')
        ->and($customerData['shipping_address']['country'])->toBe('Belgium')
        ->and($customerData['billing_address']['street'])->toBe('Billing St 2')
        ->and($customerData['billing_address']['city'])->toBe('Antwerp')
        ->and($customerData['notes'])->toBe('Please leave at the door');
});

it('can bulk delete carts', function () {
    $carts = Cart::factory()->count(3)->create();

    livewire(ListCarts::class)
        ->callTableBulkAction('hardDeleteBulk', $carts)
        ->assertNotified();

    foreach ($carts as $cart) {
        $this->assertDatabaseMissing(Cart::class, ['id' => $cart->id]);
    }
});

it('can add items to a cart via relation manager', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create();

    livewire(CartItemsRelationManager::class, [
        'ownerRecord' => $cart,
        'pageClass' => EditCart::class,
    ])
        ->callAction(TestAction::make(CreateAction::class)->table(), [
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 2,
        ])
        ->assertNotified();

    $cart->refresh();
    expect($cart->cartItems)->toHaveCount(1);

    $item = $cart->cartItems->first();
    expect($item->product_id)->toBe($product->id)
        ->and($item->product_name)->toBe($product->title)
        ->and($item->quantity)->toBe(2);
});

it('validates required fields when adding a cart item', function () {
    $cart = Cart::factory()->create();

    livewire(CartItemsRelationManager::class, [
        'ownerRecord' => $cart,
        'pageClass' => EditCart::class,
    ])
        ->callAction(TestAction::make(CreateAction::class)->table(), [
            'product_id' => null,
            'quantity' => null,
        ])
        ->assertHasFormErrors([
            'product_id' => 'required',
            'quantity' => 'required',
        ])
        ->assertNotNotified();
});

it('can add a cart item with a product variant', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 2999,
        'sku' => 'VARIANT-SKU',
    ]);
    $option = ProductVariantOption::factory()->create();
    $variant->options()->attach($option);

    livewire(CartItemsRelationManager::class, [
        'ownerRecord' => $cart,
        'pageClass' => EditCart::class,
    ])
        ->callAction(TestAction::make(CreateAction::class)->table(), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ])
        ->assertNotified();

    $cart->refresh();
    expect($cart->cartItems)->toHaveCount(1);

    $item = $cart->cartItems->first();
    expect($item->product_id)->toBe($product->id)
        ->and($item->product_variant_id)->toBe($variant->id)
        ->and($item->product_variant_label)->toBe($variant->label)
        ->and($item->unit_price)->toBe(2999)
        ->and($item->sku)->toBe('VARIANT-SKU');
});

it('can delete a cart item via relation manager', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create();
    $item = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'product_name' => $product->title,
    ]);

    livewire(CartItemsRelationManager::class, [
        'ownerRecord' => $cart,
        'pageClass' => EditCart::class,
    ])
        ->callTableAction('delete', $item->getRouteKey())
        ->assertNotified();

    $cart->refresh();
    expect($cart->cartItems)->toHaveCount(0);
});

it('can bulk delete cart items via relation manager', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create();
    $items = CartItem::factory()->count(3)->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'product_name' => $product->title,
    ]);

    livewire(CartItemsRelationManager::class, [
        'ownerRecord' => $cart,
        'pageClass' => EditCart::class,
    ])
        ->callTableBulkAction('delete', $items)
        ->assertNotified();

    $cart->refresh();
    expect($cart->cartItems)->toHaveCount(0);
});

it('denies access to cart items relation manager when user lacks permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $cart = Cart::factory()->create();

    livewire(CartItemsRelationManager::class, [
        'ownerRecord' => $cart,
        'pageClass' => EditCart::class,
    ])
        ->assertForbidden();
});

it('denies destructive bulk actions when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view carts']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $carts = Cart::factory()->count(2)->create();

    livewire(ListCarts::class)
        ->assertTableBulkActionHidden('hardDeleteBulk');

    foreach ($carts as $cart) {
        $this->assertDatabaseHas(Cart::class, ['id' => $cart->id]);
    }
});

it('denies listing carts when the user does not have view permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(ListCarts::class)
        ->assertForbidden();
});

it('denies loading create cart page when the user does not have create permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(CreateCart::class)
        ->assertForbidden();
});

it('denies loading edit cart page when the user does not have update permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view carts']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $cart = Cart::factory()->create();

    livewire(EditCart::class, ['record' => $cart->getRouteKey()])
        ->assertForbidden();
});
