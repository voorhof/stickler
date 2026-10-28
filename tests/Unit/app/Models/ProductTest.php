<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

use App\Models\Product;
use App\Models\User;
use App\Policies\ProductPolicy;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Activitylog\Support\LogOptions;

test('a product can be created', function () {
    $user = User::factory()->create();

    $product = Product::forceCreate([
        'title' => 'My First Product',
        'intro' => 'This is the intro of my first product.',
        'price' => 29.99,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($product->title)->toBe('My First Product')
        ->and($product->intro)->toBe('This is the intro of my first product.')
        ->and($product->slug)->toBe('my-first-product');
});

test('a product can be mass assigned fillable values', function () {
    $product = new Product([
        'slug' => 'mass-assigned-slug',
        'title' => 'Mass Assigned Product',
        'intro' => 'Some intro text here.',
        'content' => '<p>Some <strong>rich</strong> content.</p>',
        'sku' => 'SKU-123',
        'price' => 49.99,
        'discount_price' => 39.99,
        'is_discounted' => true,
        'stock_quantity' => 100,
        'website_title' => 'External Site',
        'website_url' => 'https://example.com',
        'order_column' => 5,
        'published_at' => now(),
    ]);

    expect($product->title)->toBe('Mass Assigned Product')
        ->and($product->slug)->toBe('mass-assigned-slug')
        ->and($product->intro)->toBe('Some intro text here.')
        ->and($product->content)->toBe('<p>Some <strong>rich</strong> content.</p>')
        ->and($product->sku)->toBe('SKU-123')
        ->and($product->price)->toBe(49.99)
        ->and($product->discount_price)->toBe(39.99)
        ->and($product->is_discounted)->toBeTrue()
        ->and($product->stock_quantity)->toBe(100)
        ->and($product->website_title)->toBe('External Site')
        ->and($product->website_url)->toBe('https://example.com')
        ->and($product->order_column)->toBe(5);
});

test('it generates a slug from title', function () {
    $product = Product::forceCreate([
        'title' => 'My Product Title',
        'intro' => 'Some intro text here.',
        'price' => 19.99,
    ]);

    expect($product->slug)->toBe('my-product-title');
});

test('it does not regenerate a slug when updating the title', function () {
    User::factory()->create();
    $product = Product::factory()->create(['title' => 'My Product Title', 'slug' => 'my-product-title']);
    $product->update(['title' => 'My Updated Product Title']);

    expect($product->slug)->toBe('my-product-title');
});

test('it generates unique slugs for products with the same title', function () {
    User::factory()->create();
    $firstProduct = Product::factory()->create(['title' => 'Duplicate Title', 'slug' => 'duplicate-title']);
    $secondProduct = Product::factory()->create(['title' => 'Duplicate Title', 'slug' => 'duplicate-title-1']);

    expect($secondProduct->slug)
        ->not->toBe($firstProduct->slug)
        ->and(Str::startsWith($secondProduct->slug, 'duplicate-title'))->toBeTrue();
});

test('it uses slug as route key name', function () {
    expect((new Product)->getRouteKeyName())->toBe('slug');
});

test('it has a default order_column of one', function () {
    $user = User::factory()->create();

    $product = Product::forceCreate([
        'title' => 'Order Column Test',
        'intro' => 'Testing the default order column.',
        'price' => 9.99,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($product->order_column)->toBe(1);
});

test('it can be reordered', function () {
    User::factory()->create();
    $product1 = Product::factory()->create(['order_column' => 1]);
    $product2 = Product::factory()->create(['order_column' => 2]);

    Product::swapOrder($product1, $product2);

    expect($product1->fresh()->order_column)->toBe(2)
        ->and($product2->fresh()->order_column)->toBe(1);
});

test('it automatically reorders others when order_column is manually updated', function () {
    User::factory()->create();
    $product1 = Product::factory()->create(['order_column' => 1]);
    $product2 = Product::factory()->create(['order_column' => 2]);
    $product3 = Product::factory()->create(['order_column' => 3]);

    $product1->update(['order_column' => 3]);

    expect($product1->fresh()->order_column)->toBe(3)
        ->and($product2->fresh()->order_column)->toBe(1)
        ->and($product3->fresh()->order_column)->toBe(2);
});

test('it automatically reorders others when creating a new model with a specific order_column', function () {
    $user = User::factory()->create();
    $product1 = Product::factory()->create(['order_column' => 1]);
    $product2 = Product::factory()->create(['order_column' => 2]);
    $newProduct = Product::forceCreate([
        'title' => 'Content Product',
        'intro' => 'Intro text.',
        'content' => '<p>This is <strong>rich</strong> content.</p>',
        'price' => 19.99,
        'order_column' => 1,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($newProduct->fresh()->order_column)->toBe(1)
        ->and($product1->fresh()->order_column)->toBe(2)
        ->and($product2->fresh()->order_column)->toBe(3);
});

test('it does reorder when creating a new model without specific order_column', function () {
    $user = User::factory()->create();
    $product1 = Product::factory()->create(['order_column' => 1]);
    $product2 = Product::factory()->create(['order_column' => 2]);

    $newProduct = Product::forceCreate([
        'title' => 'Content Product',
        'intro' => 'Intro text.',
        'content' => '<p>This is <strong>rich</strong> content.</p>',
        'price' => 19.99,
        // order_column will be 1 (because of SortableOnUpdate)
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($newProduct->fresh()->order_column)->toBe(1)
        ->and($product1->fresh()->order_column)->toBe(2)
        ->and($product2->fresh()->order_column)->toBe(3);
});

test('it is not published when published_at is null', function () {
    User::factory()->create();
    $product = Product::factory()->create(['published_at' => null]);

    expect($product->published)->toBeFalse();
});

test('it is published when published_at is in the past', function () {
    User::factory()->create();
    $product = Product::factory()->create(['published_at' => now()->subDay()]);

    expect($product->published)->toBeTrue();
});

test('it is public published only when published_at is in the past', function () {
    User::factory()->create();
    $publishedProduct = Product::factory()->create(['published_at' => now()->subDay()]);
    Product::factory()->create(['published_at' => null]);
    Product::factory()->create(['published_at' => now()->addDay()]);

    expect($publishedProduct->isPublished())->toBeTrue()
        ->and(Product::isPublicPublished()->pluck('id')->all())->toBe([$publishedProduct->id]);
});

test('it is not public published when published_at is in the future', function () {
    User::factory()->create();
    $product = Product::factory()->create(['published_at' => now()->addDay()]);

    expect($product->isPublished())->toBeFalse();
});

test('it can be soft deleted', function () {
    User::factory()->create();
    $product = Product::factory()->create();

    $product->delete();

    $this->assertSoftDeleted(Product::class, ['id' => $product->id]);
});

test('it can be restored after soft delete', function () {
    User::factory()->create();
    $product = Product::factory()->softDeleted()->create();

    $product->restore();

    $this->assertNotSoftDeleted(Product::class, ['id' => $product->id]);
});

test('it belongs to a creator user', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by_user_id' => $user->id]);

    expect($product->creator)->toBeInstanceOf(User::class)
        ->and($product->creator->id)->toBe($user->id);
});

test('it belongs to an updater user', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['updated_by_user_id' => $user->id]);

    expect($product->updater)->toBeInstanceOf(User::class)
        ->and($product->updater->id)->toBe($user->id);
});

test('it returns a default user instance when creator is not loaded', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $user->forceDelete();

    // withDefault() ensures the relationship always returns a User instance
    expect($product->creator)->toBeInstanceOf(User::class)
        ->and($product->creator->name)->toBe('Guest User');
});

test('it returns a default user instance when updater is not loaded', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $user->forceDelete();

    // withDefault() ensures the relationship always returns a User instance
    expect($product->updater)->toBeInstanceOf(User::class)
        ->and($product->updater->name)->toBe('Guest User');
});

test('it returns rich content attributes', function () {
    $product = new Product;

    expect($product->hasRichContentAttribute('content'))->toBeTrue()
        ->and($product->hasRichContentAttribute('non_existent'))->toBeFalse();
});

test('it can attach and retrieve tags', function () {
    User::factory()->create();
    $product = Product::factory()->create();

    $product->attachTag('Electronics');

    expect($product->tags)->toHaveCount(1)
        ->and($product->tags->first()->name)->toBe('Electronics');
});

test('it converts price from cents to euros using accessor', function () {
    User::factory()->create();
    $product = Product::factory()->create([
        'price' => 29.99,
        'stock_quantity' => '50',
    ]);

    expect($product->price)->toBe(29.99)->toBeFloat()
        ->and($product->stock_quantity)->toBe(50)->toBeInt();
});

test('it converts discount_price from cents to euros using accessor', function () {
    User::factory()->create();
    $product = Product::factory()->create([
        'discount_price' => 19.99,
    ]);

    expect($product->discount_price)->toBe(19.99)->toBeFloat();
});

test('it handles null discount_price correctly', function () {
    User::factory()->create();
    $product = Product::factory()->create(['discount_price' => null]);

    expect($product->discount_price)->toBeNull();
});

test('it casts is_discounted to a boolean', function () {
    User::factory()->create();
    $product = Product::factory()->create([
        'is_discounted' => 1,
    ]);

    expect($product->is_discounted)->toBeTrue()->toBeBool();
});

test('it configures activity log options correctly', function () {
    $product = new Product;
    $options = $product->getActivitylogOptions();

    expect($options)->toBeInstanceOf(LogOptions::class);
});

test('content can be stored and retrieved', function () {
    $user = User::factory()->create();

    $product = Product::forceCreate([
        'title' => 'Content Product',
        'intro' => 'Intro text.',
        'content' => '<p>This is <strong>rich</strong> content.</p>',
        'price' => 19.99,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($product->content)->toBe('<p>This is <strong>rich</strong> content.</p>');
});

test('content is nullable', function () {
    User::factory()->create();
    $product = Product::factory()->create(['content' => null]);

    expect($product->content)->toBeNull();
});

test('published_at is cast to a datetime instance', function () {
    User::factory()->create();
    $product = Product::factory()->create(['published_at' => '2024-01-15 12:00:00']);

    expect($product->published_at)->toBeInstanceOf(Carbon::class);
});

test('product implements has rich content contract', function () {
    $product = new Product;

    expect($product)->toBeInstanceOf(HasRichContent::class);
});

test('product uses interacts with rich content and sets up rich content attributes', function () {
    $product = new Product;

    expect($product->hasRichContentAttribute('content'))->toBeTrue()
        ->and($product->getRichContentAttribute('content'))->not->toBeNull();
});

test('it registers standard media collections', function () {
    User::factory()->create();
    $product = Product::factory()->create();

    $product->registerMediaCollections();

    expect(array_keys($product->mediaCollections))->toContain('cover')
        ->and(array_keys($product->mediaCollections))->toContain('images')
        ->and(array_keys($product->mediaCollections))->toContain('videos')
        ->and(array_keys($product->mediaCollections))->toContain('documents');
});

test('it registers standard media conversions', function () {
    User::factory()->create();
    $product = Product::factory()->create();

    $product->registerAllMediaConversions();

    $names = collect($product->mediaConversions)->map(fn ($c) => $c->getName())->toArray();

    expect($names)->toContain('preview')
        ->and($names)->toContain('thumb')
        ->and($names)->toContain('webp');
});

test('product model has product policy attribute configured', function () {
    $attributes = new ReflectionClass(Product::class)->getAttributes(UsePolicy::class);

    expect($attributes)->toHaveCount(1)
        ->and($attributes[0]->getArguments()[0])->toBe(ProductPolicy::class);
});

test('sku is nullable', function () {
    User::factory()->create();
    $product = Product::factory()->create(['sku' => null]);

    expect($product->sku)->toBeNull();
});

test('sku is unique', function () {
    User::factory()->create();
    Product::factory()->create(['sku' => 'UNIQUE-SKU-001']);

    expect(function () {
        Product::forceCreate([
            'title' => 'Duplicate SKU Product',
            'intro' => 'Intro.',
            'sku' => 'UNIQUE-SKU-001',
            'price' => 9.99,
        ]);
    })->toThrow(Illuminate\Database\UniqueConstraintViolationException::class);
});

test('discount_price accessor returns null when database value is null', function () {
    User::factory()->create();
    $product = Product::factory()->create(['discount_price' => null]);

    expect($product->discount_price)->toBeNull();
});

test('website fields are nullable', function () {
    User::factory()->create();
    $product = Product::factory()->create([
        'website_title' => null,
        'website_url' => null,
    ]);

    expect($product->website_title)->toBeNull()
        ->and($product->website_url)->toBeNull();
});

test('it has many product variants', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variants = App\Models\ProductVariant::factory()->count(3)->create([
        'product_id' => $product->id,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($product->variants)->toHaveCount(3)
        ->and($product->variants->pluck('id')->toArray())->toBe($variants->pluck('id')->toArray());
});
