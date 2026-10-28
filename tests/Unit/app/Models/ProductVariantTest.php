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
