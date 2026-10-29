<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Imports\ProductImporter;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Imports\Models\Import;

describe('importing products', function () {
    test('product importer imports valid row with all fields', function () {
        User::factory()->create();

        ProductImporter::test()
            ->import([
                'slug' => 'test-product',
                'title' => 'Test Product',
                'intro' => 'This is a test product intro.',
                'sku' => 'PRD-001',
                'price' => 99.99,
                'discount_price' => 79.99,
                'is_discounted' => true,
                'stock_quantity' => 50,
                'website_title' => 'External Product',
                'website_url' => 'https://example.com/product',
                'published_at' => '2026-10-21 12:00:00',
            ])
            ->assertImported();

        $product = Product::where('slug', 'test-product')->first();
        expect($product->title)->toBe('Test Product')
            ->and($product->sku)->toBe('PRD-001')
            ->and($product->price)->toBe(99.99)
            ->and($product->discount_price)->toBe(79.99)
            ->and($product->is_discounted)->toBeTrue()
            ->and($product->stock_quantity)->toBe(50)
            ->and($product->website_title)->toBe('External Product')
            ->and($product->website_url)->toBe('https://example.com/product');
    });

    test('product importer imports valid row without optional fields', function () {
        User::factory()->create();

        ProductImporter::test()
            ->import([
                'slug' => 'minimal-product',
                'title' => 'Minimal Product',
                'intro' => 'Minimal intro.',
                'price' => 49.99,
                'stock_quantity' => 10,
            ])
            ->assertImported();

        $product = Product::where('slug', 'minimal-product')->first();
        expect($product->title)->toBe('Minimal Product')
            ->and($product->sku)->toBeNull()
            ->and($product->price)->toBe(49.99)
            ->and($product->discount_price)->toBeNull()
            ->and($product->stock_quantity)->toBe(10);
    });

    test('product importer fails validation with invalid price', function () {
        ProductImporter::test()
            ->import([
                'slug' => 'invalid-price-product',
                'title' => 'Invalid Price Product',
                'intro' => 'Intro.',
                'price' => 'not-a-number',
                'stock_quantity' => 0,
            ])
            ->assertHasErrors(['price' => 'numeric']);
    });

    test('product importer fails validation with negative price', function () {
        ProductImporter::test()
            ->import([
                'slug' => 'negative-price-product',
                'title' => 'Negative Price Product',
                'intro' => 'Intro.',
                'price' => -10,
                'stock_quantity' => 0,
            ])
            ->assertHasErrors(['price' => 'min:0']);
    });

    test('product importer fails validation with invalid discount price', function () {
        ProductImporter::test()
            ->import([
                'slug' => 'invalid-discount-product',
                'title' => 'Invalid Discount Product',
                'intro' => 'Intro.',
                'price' => 99.99,
                'discount_price' => 'not-a-number',
                'stock_quantity' => 0,
            ])
            ->assertHasErrors(['discount_price' => 'numeric']);
    });

    test('product importer fails validation with invalid website url', function () {
        ProductImporter::test()
            ->import([
                'slug' => 'invalid-url-product',
                'title' => 'Invalid URL Product',
                'intro' => 'Intro.',
                'price' => 99.99,
                'stock_quantity' => 0,
                'website_url' => 'not-a-url',
            ])
            ->assertHasErrors(['website_url' => 'url']);
    });

    test('product importer validates date rule and imports valid published at', function () {
        User::factory()->create();

        ProductImporter::test()
            ->import([
                'slug' => 'published-product',
                'title' => 'Published Product',
                'intro' => 'Intro.',
                'price' => 99.99,
                'stock_quantity' => 0,
                'published_at' => '2026-10-21 12:00:00',
            ])
            ->assertImported();

        expect(Product::where('slug', 'published-product')->exists())->toBeTrue();
    });

    test('product importer fails validation with invalid date', function () {
        ProductImporter::test()
            ->import([
                'slug' => 'invalid-date-product',
                'title' => 'Invalid Date Product',
                'intro' => 'Intro.',
                'price' => 99.99,
                'stock_quantity' => 0,
                'published_at' => 'not-a-date',
            ])
            ->assertHasErrors(['published_at' => 'date']);
    });

    test('product importer allows null or empty published at', function () {
        User::factory()->create();

        ProductImporter::test()
            ->import([
                'slug' => 'unpublished-product',
                'title' => 'Unpublished Product',
                'intro' => 'Intro.',
                'price' => 99.99,
                'stock_quantity' => 0,
                'published_at' => null,
            ])
            ->assertImported();

        expect(Product::where('slug', 'unpublished-product')->first()->published_at)->toBeNull();
    });
});

describe('user tracking', function () {
    test('product importer sets created by and updated by user id to the user executing the import', function () {
        $user = User::factory()->create();

        $import = new Import([
            'user_id' => $user->id,
        ]);

        ProductImporter::test(import: $import)
            ->import([
                'slug' => 'test-product-user-id',
                'title' => 'Test Product User ID',
                'intro' => 'This is a test product intro.',
                'price' => 99.99,
                'stock_quantity' => 0,
            ])
            ->assertImported();

        $product = Product::where('slug', 'test-product-user-id')->first();
        expect($product->created_by_user_id)->toBe($user->id)
            ->and($product->updated_by_user_id)->toBe($user->id);
    });

    test('product importer sets created by and updated by user id from authenticated user', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        ProductImporter::test()
            ->import([
                'slug' => 'test-product-auth-user',
                'title' => 'Test Product Auth User',
                'intro' => 'This is a test product intro.',
                'price' => 99.99,
                'stock_quantity' => 0,
            ])
            ->assertImported();

        $product = Product::where('slug', 'test-product-auth-user')->first();
        expect($product->created_by_user_id)->toBe($user->id)
            ->and($product->updated_by_user_id)->toBe($user->id);
    });
});
