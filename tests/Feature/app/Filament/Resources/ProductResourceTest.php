<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Actions\HardDeleteAction;
use App\Filament\Actions\RestoreDeletedAction;
use App\Filament\Actions\SoftDeleteAction;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Category;
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
        'create products',
        'view products',
        'update products',
        'delete products',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('can list products', function () {
    $products = Product::factory()->count(3)->create();

    livewire(ListProducts::class)
        ->assertOk()
        ->assertCanSeeTableRecords($products)
        ->assertCountTableRecords(3);
});

it('can search products by title', function () {
    Product::factory()->create(['title' => 'Unique Product Title', 'slug' => 'unique-product-title']);
    $otherProduct = Product::factory()->create(['title' => 'Something Else', 'slug' => 'something-else']);

    livewire(ListProducts::class)
        ->searchTable('Unique Product Title')
        ->assertCanSeeTableRecords(Product::where('title', 'Unique Product Title')->get())
        ->assertCanNotSeeTableRecords([$otherProduct]);
});

it('can filter products by trashed state on the list page table', function () {
    $activeProduct = Product::factory()->create();
    $trashedProduct = Product::factory()->softDeleted()->create();

    livewire(ListProducts::class)
        ->filterTable('trashed', false)
        ->assertCanSeeTableRecords([$trashedProduct])
        ->assertCanNotSeeTableRecords([$activeProduct])
        ->filterTable('trashed', true)
        ->assertCanSeeTableRecords([$activeProduct, $trashedProduct]);
});

it('validates required fields when creating a product', function () {
    livewire(CreateProduct::class)
        ->fillForm([
            'title' => null,
            'intro' => null,
            'price' => null,
            'stock_quantity' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'title' => 'required',
            'intro' => 'required',
            'price' => 'required',
            'stock_quantity' => 'required',
        ])
        ->assertNotNotified();
});

it('can create a product with all product info fields', function () {
    livewire(CreateProduct::class)
        ->fillForm([
            'title' => 'New Product',
            'intro' => 'A new product intro.',
            'content' => '<p>Product content.</p>',
            'sku' => 'PRD-NEW-001',
            'price' => 149.99,
            'discount_price' => 119.99,
            'is_discounted' => true,
            'stock_quantity' => 25,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(Product::class, [
        'title' => 'New Product',
        'slug' => 'new-product',
        'sku' => 'PRD-NEW-001',
        'is_discounted' => true,
        'stock_quantity' => 25,
    ]);

    $product = Product::where('slug', 'new-product')->first();
    expect($product->price)->toBe(149.99)
        ->and($product->discount_price)->toBe(119.99);
});

it('can create a product without optional product info fields', function () {
    livewire(CreateProduct::class)
        ->fillForm([
            'title' => 'Minimal Product',
            'intro' => 'Minimal intro.',
            'price' => 29.99,
            'stock_quantity' => 0,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(Product::class, [
        'title' => 'Minimal Product',
        'slug' => 'minimal-product',
    ]);

    $product = Product::where('slug', 'minimal-product')->first();
    expect($product->sku)->toBeNull()
        ->and($product->price)->toBe(29.99)
        ->and($product->discount_price)->toBeNull()
        ->and($product->is_discounted)->toBeFalse()
        ->and($product->stock_quantity)->toBe(0);
});

it('validates required fields when editing a product', function () {
    $product = Product::factory()->create();

    livewire(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm([
            'title' => null,
            'intro' => null,
            'price' => null,
            'stock_quantity' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'title' => 'required',
            'intro' => 'required',
            'price' => 'required',
            'stock_quantity' => 'required',
        ])
        ->assertNotNotified();
});

it('can edit a product with product info fields', function () {
    $product = Product::factory()->create([
        'title' => 'Old Title',
        'sku' => 'PRD-OLD',
        'price' => 49.99,
        'discount_price' => null,
        'is_discounted' => false,
        'stock_quantity' => 5,
    ]);

    livewire(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm([
            'title' => 'Updated Title',
            'sku' => 'PRD-UPDATED',
            'price' => 59.99,
            'discount_price' => 49.99,
            'is_discounted' => true,
            'stock_quantity' => 15,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(Product::class, [
        'id' => $product->id,
        'title' => 'Updated Title',
        'sku' => 'PRD-UPDATED',
        'is_discounted' => true,
        'stock_quantity' => 15,
    ]);

    $product->refresh();
    expect($product->price)->toBe(59.99)
        ->and($product->discount_price)->toBe(49.99);
});

it('can soft delete a product', function () {
    $product = Product::factory()->create();

    livewire(EditProduct::class, ['record' => $product->getRouteKey()])
        ->callAction(SoftDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertSoftDeleted(Product::class, ['id' => $product->id]);
});

it('can force delete a soft-deleted product', function () {
    $product = Product::factory()->softDeleted()->create();

    livewire(EditProduct::class, ['record' => $product->getRouteKey()])
        ->callAction(HardDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertDatabaseMissing(Product::class, ['id' => $product->id]);
});

it('can restore a soft-deleted product', function () {
    $product = Product::factory()->softDeleted()->create();

    livewire(EditProduct::class, ['record' => $product->getRouteKey()])
        ->callAction(RestoreDeletedAction::class)
        ->assertNotified();

    $this->assertNotSoftDeleted(Product::class, ['id' => $product->id]);
});

it('can bulk delete products', function () {
    $products = Product::factory()->count(2)->create();

    livewire(ListProducts::class)
        ->callTableBulkAction('softDeleteBulk', $products)
        ->assertNotified();

    foreach ($products as $product) {
        $this->assertSoftDeleted(Product::class, ['id' => $product->id]);
    }
});

it('can bulk force delete soft-deleted products', function () {
    $products = Product::factory()->count(2)->softDeleted()->create();

    livewire(ListProducts::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('hardDeleteBulk', $products)
        ->assertNotified();

    foreach ($products as $product) {
        $this->assertDatabaseMissing(Product::class, ['id' => $product->id]);
    }
});

it('can bulk restore soft-deleted products', function () {
    $products = Product::factory()->count(2)->softDeleted()->create();

    livewire(ListProducts::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('restoreDeletedBulk', $products)
        ->assertNotified();

    foreach ($products as $product) {
        $this->assertNotSoftDeleted(Product::class, ['id' => $product->id]);
    }
});

it('denies destructive bulk actions when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view products']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $activeProduct = Product::factory()->create();
    $trashedProduct = Product::factory()->softDeleted()->create();

    livewire(ListProducts::class)
        ->assertTableBulkActionHidden('softDeleteBulk');

    livewire(ListProducts::class)
        ->filterTable('trashed', false)
        ->assertTableBulkActionHidden('restoreDeletedBulk')
        ->assertTableBulkActionHidden('hardDeleteBulk');

    $this->assertDatabaseHas(Product::class, ['id' => $activeProduct->id, 'deleted_at' => null]);
    $this->assertSoftDeleted(Product::class, ['id' => $trashedProduct->id]);
});

it('denies listing products when the user does not have view permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(ListProducts::class)
        ->assertForbidden();
});

it('denies loading create product page when the user does not have create permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(CreateProduct::class)
        ->assertForbidden();
});

it('denies loading edit product page when the user does not have update permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view products']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $product = Product::factory()->create();

    livewire(EditProduct::class, ['record' => $product->getRouteKey()])
        ->assertForbidden();
});

it('can create a product with variants', function () {
    $colorType = App\Models\ProductVariantOptionType::factory()->create(['name' => 'Color']);
    $redOption = App\Models\ProductVariantOption::factory()->create([
        'product_variant_option_type_id' => $colorType->id,
        'name' => 'Red',
    ]);

    livewire(CreateProduct::class)
        ->fillForm([
            'title' => 'Product with Variants',
            'intro' => 'A product with variants.',
            'price' => 29.99,
            'stock_quantity' => 10,
            'variants' => [
                [
                    'sku' => 'PRD-VAR-RED',
                    'price' => 29.99,
                    'discount_price' => null,
                    'is_discounted' => false,
                    'stock_quantity' => 5,
                    'options' => [$redOption->id],
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(Product::class, [
        'title' => 'Product with Variants',
    ]);
    $this->assertDatabaseHas('product_variants', [
        'sku' => 'PRD-VAR-RED',
    ]);
});

it('can edit a product with variants', function () {
    $product = Product::factory()->create();
    $colorType = App\Models\ProductVariantOptionType::factory()->create(['name' => 'Size']);
    $largeOption = App\Models\ProductVariantOption::factory()->create([
        'product_variant_option_type_id' => $colorType->id,
        'name' => 'Large',
    ]);

    livewire(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm([
            'variants' => [
                [
                    'sku' => 'PRD-VAR-LARGE',
                    'price' => 39.99,
                    'discount_price' => null,
                    'is_discounted' => false,
                    'stock_quantity' => 10,
                    'options' => [$largeOption->id],
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas('product_variants', [
        'sku' => 'PRD-VAR-LARGE',
    ]);
});

it('validates variant price cannot exceed product price', function () {
    livewire(CreateProduct::class)
        ->fillForm([
            'title' => 'Product with Expensive Variant',
            'intro' => 'Variant price exceeds product price.',
            'price' => 29.99,
            'stock_quantity' => 10,
            'variants' => [
                [
                    'sku' => 'PRD-VAR-EXPENSIVE',
                    'price' => 99.99,
                    'discount_price' => null,
                    'is_discounted' => false,
                    'stock_quantity' => 5,
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors([
            'variants.0.price' => 'max',
        ])
        ->assertNotNotified();
});

it('allows variant price equal to product price', function () {
    livewire(CreateProduct::class)
        ->fillForm([
            'title' => 'Product with Equal Variant Price',
            'intro' => 'Variant price equals product price.',
            'price' => 29.99,
            'stock_quantity' => 10,
            'variants' => [
                [
                    'sku' => 'PRD-VAR-EQUAL',
                    'price' => 29.99,
                    'discount_price' => null,
                    'is_discounted' => false,
                    'stock_quantity' => 5,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(Product::class, [
        'title' => 'Product with Equal Variant Price',
    ]);
});

it('can create a product with categories', function () {
    $categories = Category::factory()->count(2)->create(['for_products' => true]);

    livewire(CreateProduct::class)
        ->fillForm([
            'title' => 'Product with Categories',
            'intro' => 'A product with categories.',
            'price' => 29.99,
            'stock_quantity' => 10,
            'categories' => $categories->pluck('id')->toArray(),
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $product = Product::where('title', 'Product with Categories')->first();
    expect($product->categories->pluck('id')->sort()->toArray())
        ->toBe($categories->pluck('id')->sort()->toArray());
});

it('can edit product categories', function () {
    $product = Product::factory()->create();
    $newCategory = Category::factory()->create(['for_products' => true]);

    livewire(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm([
            'categories' => [$newCategory->id],
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $product->refresh();
    expect($product->categories->pluck('id')->toArray())
        ->toBe([$newCategory->id]);
});

it('denies deleting a product when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view products', 'update products']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $product = Product::factory()->create();

    livewire(EditProduct::class, ['record' => $product->getRouteKey()])
        ->assertActionHidden(SoftDeleteAction::class);

    $this->assertDatabaseHas(Product::class, ['id' => $product->id]);
});
