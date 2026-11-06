<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantOption;
use App\Models\ProductVariantOptionType;
use App\Models\User;
use App\Observers\ProductVariantObserver;
use App\Policies\ProductVariantPolicy;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;

test('a product variant can be created', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $variant = ProductVariant::forceCreate([
        'product_id' => $product->id,
        'sku' => 'PRD-001-RED',
        'price' => 29.99,
        'discount_price' => null,
        'is_discounted' => false,
        'stock_quantity' => 50,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($variant->sku)->toBe('PRD-001-RED')
        ->and($variant->stock_quantity)->toBe(50);
});

test('a product variant can be mass assigned fillable values', function () {
    $variant = new ProductVariant([
        'sku' => 'PRD-002-BLUE',
        'price' => 49.99,
        'discount_price' => 39.99,
        'is_discounted' => true,
        'stock_quantity' => 100,
    ]);

    expect($variant->sku)->toBe('PRD-002-BLUE')
        ->and($variant->is_discounted)->toBeTrue()
        ->and($variant->stock_quantity)->toBe(100);
});

test('it belongs to a product', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['title' => 'T-Shirt']);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($variant->product)->toBeInstanceOf(Product::class)
        ->and($variant->product->id)->toBe($product->id)
        ->and($variant->product->title)->toBe('T-Shirt');
});

test('it has many variant options', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);
    $type = ProductVariantOptionType::factory()->create();
    $options = ProductVariantOption::factory()->count(2)->create([
        'product_variant_option_type_id' => $type->id,
    ]);

    $variant->options()->attach($options->pluck('id'));

    expect($variant->options)->toHaveCount(2)
        ->and($variant->options->pluck('id')->toArray())->toBe($options->pluck('id')->toArray());
});

test('it converts price from cents to euros using accessor', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 29.99,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($variant->price)->toBe(29.99)->toBeFloat();
});

test('it converts discount_price from cents to euros using accessor', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'discount_price' => 19.99,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($variant->discount_price)->toBe(19.99)->toBeFloat();
});

test('it handles null discount_price correctly', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'discount_price' => null,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($variant->discount_price)->toBeNull();
});

test('it casts is_discounted to a boolean', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'is_discounted' => 1,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($variant->is_discounted)->toBeTrue()->toBeBool();
});

test('it casts stock_quantity to an integer', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'stock_quantity' => '50',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($variant->stock_quantity)->toBe(50)->toBeInt();
});

test('it belongs to a creator user', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($variant->creator)->toBeInstanceOf(User::class)
        ->and($variant->creator->id)->toBe($user->id);
});

test('it belongs to an updater user', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'updated_by_user_id' => $user->id,
        'created_by_user_id' => $user->id,
    ]);

    expect($variant->updater)->toBeInstanceOf(User::class)
        ->and($variant->updater->id)->toBe($user->id);
});

test('it returns a default user instance when creator is not loaded', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    $user->forceDelete();

    expect($variant->creator)->toBeInstanceOf(User::class)
        ->and($variant->creator->name)->toBe('Guest User');
});

test('it returns a default user instance when updater is not loaded', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    $user->forceDelete();

    expect($variant->updater)->toBeInstanceOf(User::class)
        ->and($variant->updater->name)->toBe('Guest User');
});

test('sku is nullable', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'sku' => null,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($variant->sku)->toBeNull();
});

test('sku is unique', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'sku' => 'UNIQUE-VARIANT-SKU',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect(function () use ($product) {
        ProductVariant::forceCreate([
            'product_id' => $product->id,
            'sku' => 'UNIQUE-VARIANT-SKU',
            'price' => 9.99,
        ]);
    })->toThrow(Illuminate\Database\UniqueConstraintViolationException::class);
});

test('product variant model has observer attribute configured', function () {
    $attributes = new ReflectionClass(ProductVariant::class)->getAttributes(ObservedBy::class);

    expect($attributes)->toHaveCount(1)
        ->and($attributes[0]->getArguments()[0])->toBe(ProductVariantObserver::class);
});

test('product variant model has policy attribute configured', function () {
    $attributes = new ReflectionClass(ProductVariant::class)->getAttributes(UsePolicy::class);

    expect($attributes)->toHaveCount(1)
        ->and($attributes[0]->getArguments()[0])->toBe(ProductVariantPolicy::class);
});

test('tax rate is nullable', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'tax_rate' => null,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($variant->tax_rate)->toBeNull();
});

test('tax rate can be set to a specific value', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'tax_rate' => 6,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($variant->tax_rate)->toBe(6);
});

test('getTaxRate returns variant tax rate when set', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'tax_rate' => 6,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($variant->getTaxRate())->toBe(6);
});

test('getTaxRate falls back to product tax rate when variant tax rate is null', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['tax_rate' => 6]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'tax_rate' => null,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);
    $variant->setRelation('product', $product);

    expect($variant->getTaxRate())->toBe(6);
});

test('getTaxRate falls back to global default when both variant and product tax rate are null', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['tax_rate' => null]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'tax_rate' => null,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);
    $variant->setRelation('product', $product);

    expect($variant->getTaxRate())->toBe(21);
});

test('priceNet returns net price excluding VAT', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    // €121.00 gross at 21% VAT = €100.00 net
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 121.00,
        'tax_rate' => 21,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($variant->priceNet())->toBe(100.0);
});

test('priceNet returns net price with reduced VAT rate', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    // €106.00 gross at 6% VAT = €100.00 net
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 106.00,
        'tax_rate' => 6,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($variant->priceNet())->toBe(100.0);
});

test('taxAmount returns VAT amount', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    // €121.00 gross at 21% VAT = €21.00 tax
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 121.00,
        'tax_rate' => 21,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($variant->taxAmount())->toBe(21.0);
});

test('taxAmount returns zero when VAT rate is zero', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 50.00,
        'tax_rate' => 0,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($variant->taxAmount())->toBe(0.0);
});

// --------------------------------------------------------------------------
// Stock Helper Methods
// --------------------------------------------------------------------------

test('variant isInStock returns true when stock is positive', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'stock_quantity' => 3,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($variant->isInStock())->toBeTrue()
        ->and($variant->isOutOfStock())->toBeFalse()
        ->and($variant->canFulfillQuantity(2))->toBeTrue()
        ->and($variant->canFulfillQuantity(3))->toBeTrue()
        ->and($variant->canFulfillQuantity(4))->toBeFalse();
});

test('variant isInStock returns false when stock is zero', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'stock_quantity' => 0,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($variant->isInStock())->toBeFalse()
        ->and($variant->isOutOfStock())->toBeTrue()
        ->and($variant->canFulfillQuantity(1))->toBeFalse();
});

// --------------------------------------------------------------------------
// Label Accessor
// --------------------------------------------------------------------------

test('label accessor returns option names when options are loaded', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);
    $type = ProductVariantOptionType::factory()->create();
    $option1 = ProductVariantOption::create(['product_variant_option_type_id' => $type->id, 'name' => 'Red', 'value' => '#ff0000']);
    $option2 = ProductVariantOption::create(['product_variant_option_type_id' => $type->id, 'name' => 'Large', 'value' => 'L']);

    $variant->options()->attach([$option1->id, $option2->id]);
    $variant->load('options');

    expect($variant->label)->toBe('Red / Large');
});

test('label accessor returns empty string when options are not loaded', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($variant->label)->toBe('');
});
