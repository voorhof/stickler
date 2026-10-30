<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Jobs\GenerateSitemapJob;
use App\Models\Media;
use App\Models\Product;
use App\Models\User;
use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Model;

use function Illuminate\Support\defer;

use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;

use Spatie\ResponseCache\Facades\ResponseCache;

it('tracks creator and updater on product create and update', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $product = Product::create([
        'title' => 'Observer Test Product',
        'intro' => 'Testing observer tracking.',
        'price' => 19.99,
        'discount_price' => null,
    ]);

    expect($product->created_by_user_id)->toBe($creator->id)
        ->and($product->updated_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $product->update(['title' => 'Updated Title']);

    expect($product->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($product->fresh()->updated_by_user_id)->toBe($updater->id);
});

it('syncs image alt text to media name when a product is saved with rich content', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $uuid = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b';

    $product = Product::create([
        'title' => 'Product with Rich Content Image',
        'intro' => 'Intro text',
        'content' => '<p><img src="/storage/test.jpg" alt="Observer Test Alt" data-id="'.$uuid.'"></p>',
        'price' => 29.99,
        'discount_price' => null,
    ]);

    $media = Media::create([
        'uuid' => $uuid,
        'model_type' => $product->getMorphClass(),
        'model_id' => $product->getKey(),
        'collection_name' => 'content',
        'name' => 'original-name.jpg',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    expect($media->name)->toBe('original-name.jpg');

    // Trigger saved event by updating product
    $product->update([
        'title' => 'Updated Product Title',
    ]);

    expect($media->fresh()->name)->toBe('Observer Test Alt');
});

it('calls syncImageAltToMediaName directly via observer saved method', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $uuid = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b';

    $product = Product::create([
        'title' => 'Product Observer Direct Test',
        'intro' => 'Intro text',
        'content' => '<p><img src="/storage/test.jpg" alt="Direct Observer Alt" data-id="'.$uuid.'"></p>',
        'price' => 19.99,
        'discount_price' => null,
    ]);

    $media = Media::create([
        'uuid' => $uuid,
        'model_type' => $product->getMorphClass(),
        'model_id' => $product->getKey(),
        'collection_name' => 'content',
        'name' => 'old-name.jpg',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $observer = new ProductObserver;
    $observer->saved($product);

    expect($media->fresh()->name)->toBe('Direct Observer Alt');
});

it('queues sitemap generation when a product is created', function (): void {
    $user = User::factory()->create();
    Queue::fake([GenerateSitemapJob::class]);
    actingAs($user);

    Product::create([
        'title' => 'Sitemap Observer Test Product',
        'intro' => 'Testing sitemap generation.',
        'price' => 9.99,
        'discount_price' => null,
    ]);

    Queue::assertPushed(GenerateSitemapJob::class);
});

it('queues sitemap generation when a product is deleted', function (): void {
    $user = User::factory()->create();
    $product = Model::withoutEvents(fn (): Product => Product::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]));
    Queue::fake([GenerateSitemapJob::class]);

    $product->delete();

    Queue::assertPushed(GenerateSitemapJob::class);
});

it('flushes response cache when a product is saved', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    config(['responsecache.enabled' => true]);

    ResponseCache::shouldReceive('clear')->once();

    Product::create([
        'title' => 'Cache Flush Test Product',
        'intro' => 'Testing cache flush on save.',
        'price' => 9.99,
        'discount_price' => null,
    ]);

    defer()->invoke();

    ResponseCache::shouldReceive('clear')->once();

    Product::first()->update(['title' => 'Cache Flush Updated Product']);

    defer()->invoke();
});

it('flushes response cache when a product is deleted', function (): void {
    $user = User::factory()->create();
    $product = Model::withoutEvents(fn (): Product => Product::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]));

    config(['responsecache.enabled' => true]);
    ResponseCache::shouldReceive('clear')->once();

    $product->delete();

    defer()->invoke();
});

it('disables activity logging on second save for newly created products', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $product = Product::create([
        'title' => 'Activity Log Test Product',
        'intro' => 'Testing activity log suppression.',
        'price' => 19.99,
        'discount_price' => null,
    ]);

    $observer = new ProductObserver;

    // Simulate a newly created product
    $product->wasRecentlyCreated = true;

    // The observer should call disableLogging on the model
    $observer->saved($product);

    // If disableLogging was called, enableLoggingModelsEvents should be false
    expect($product->enableLoggingModelsEvents)->toBeFalse();
});

it('does not disable activity logging for existing products', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $product = Product::create([
        'title' => 'Existing Product',
        'intro' => 'Testing existing product.',
        'content' => '<p>Some content</p>',
        'price' => 19.99,
        'discount_price' => null,
    ]);

    $observer = new ProductObserver;

    // Simulate an existing product (not newly created)
    $product->wasRecentlyCreated = false;
    $product->enableLoggingModelsEvents = true;

    $observer->saved($product);

    // Logging should remain enabled
    expect($product->enableLoggingModelsEvents)->toBeTrue();
});
