<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantOption;
use App\Models\ProductVariantOptionType;
use App\Policies\ProductVariantOptionPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;

test('a variant option can be created', function () {
    $type = ProductVariantOptionType::factory()->create();

    $option = ProductVariantOption::forceCreate([
        'product_variant_option_type_id' => $type->id,
        'name' => 'Red',
        'value' => '#FF0000',
    ]);

    expect($option->name)->toBe('Red')
        ->and($option->value)->toBe('#FF0000');
});

test('a variant option can be mass assigned fillable values', function () {
    $type = ProductVariantOptionType::factory()->create();

    $option = new ProductVariantOption([
        'product_variant_option_type_id' => $type->id,
        'name' => 'Large',
        'value' => null,
    ]);

    expect($option->name)->toBe('Large')
        ->and($option->value)->toBeNull();
});

test('it belongs to a variant option type', function () {
    $type = ProductVariantOptionType::factory()->create(['name' => 'Color']);
    $option = ProductVariantOption::forceCreate([
        'product_variant_option_type_id' => $type->id,
        'name' => 'Red',
        'value' => '#FF0000',
    ]);

    $option->refresh();

    expect($option->type)->toBeInstanceOf(ProductVariantOptionType::class)
        ->and($option->type->id)->toBe($type->id)
        ->and($option->type->name)->toBe('Color');
});

test('it has many product variants', function () {
    $type = ProductVariantOptionType::factory()->create();
    $option = ProductVariantOption::forceCreate([
        'product_variant_option_type_id' => $type->id,
        'name' => 'Large',
    ]);
    $user = App\Models\User::factory()->create();
    $product = Product::factory()->create();
    $variants = ProductVariant::factory()->count(2)->create([
        'product_id' => $product->id,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    $option->productVariants()->attach($variants->pluck('id'));

    expect($option->productVariants)->toHaveCount(2)
        ->and($option->productVariants->pluck('id')->toArray())->toBe($variants->pluck('id')->toArray());
});

test('variant option value is nullable', function () {
    $type = ProductVariantOptionType::factory()->create();
    $option = ProductVariantOption::forceCreate([
        'product_variant_option_type_id' => $type->id,
        'name' => 'Small',
        'value' => null,
    ]);

    expect($option->value)->toBeNull();
});

test('variant option model has policy attribute configured', function () {
    $attributes = new ReflectionClass(ProductVariantOption::class)->getAttributes(UsePolicy::class);

    expect($attributes)->toHaveCount(1)
        ->and($attributes[0]->getArguments()[0])->toBe(ProductVariantOptionPolicy::class);
});
