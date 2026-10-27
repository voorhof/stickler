<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Jobs\GenerateSitemapJob;
use App\Models\Media;
use App\Models\Product;
use App\Models\User;
use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;

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
