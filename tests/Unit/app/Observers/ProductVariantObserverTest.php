<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

use function Illuminate\Support\defer;
use function Pest\Laravel\actingAs;

use Spatie\ResponseCache\Facades\ResponseCache;

it('tracks creator and updater on variant create and update', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();
    $product = Product::factory()->create();

    actingAs($creator);

    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'sku' => 'OBS-TEST-001',
        'price' => 19.99,
        'stock_quantity' => 10,
    ]);

    expect($variant->created_by_user_id)->toBe($creator->id)
        ->and($variant->updated_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $variant->update(['sku' => 'OBS-TEST-UPDATED']);

    expect($variant->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($variant->fresh()->updated_by_user_id)->toBe($updater->id);
});

it('flushes response cache when a product variant is saved', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    actingAs($user);

    config(['responsecache.enabled' => true]);

    ResponseCache::shouldReceive('clear')->once();

    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'sku' => 'CACHE-TEST-001',
        'price' => 29.99,
        'stock_quantity' => 5,
    ]);

    defer()->invoke();

    ResponseCache::shouldReceive('clear')->once();

    $variant->update(['sku' => 'CACHE-TEST-UPDATED']);

    defer()->invoke();
});

it('flushes response cache when a product variant is deleted', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = Model::withoutEvents(fn (): ProductVariant => ProductVariant::factory()->create([
        'product_id' => $product->id,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]));

    config(['responsecache.enabled' => true]);
    ResponseCache::shouldReceive('clear')->once();

    $variant->delete();

    defer()->invoke();
});
