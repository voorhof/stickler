<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

use App\Models\ProductVariantOption;
use App\Models\ProductVariantOptionType;
use App\Policies\ProductVariantOptionTypePolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;

test('a variant option type can be created', function () {
    $type = ProductVariantOptionType::forceCreate([
        'name' => 'Color',
    ]);

    expect($type->name)->toBe('Color');
});

test('a variant option type can be mass assigned fillable values', function () {
    $type = new ProductVariantOptionType([
        'name' => 'Size',
    ]);

    expect($type->name)->toBe('Size');
});

test('it has many variant options', function () {
    $type = ProductVariantOptionType::factory()->create();
    $options = ProductVariantOption::factory()->count(3)->create([
        'product_variant_option_type_id' => $type->id,
    ]);

    expect($type->options)->toHaveCount(3)
        ->and($type->options->pluck('id')->toArray())->toBe($options->pluck('id')->toArray());
});

test('variant option type model has policy attribute configured', function () {
    $attributes = new ReflectionClass(ProductVariantOptionType::class)->getAttributes(UsePolicy::class);

    expect($attributes)->toHaveCount(1)
        ->and($attributes[0]->getArguments()[0])->toBe(ProductVariantOptionTypePolicy::class);
});
