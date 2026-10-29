<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Exports\ProductExporter;
use App\Models\Product;
use App\Models\User;

describe('exporting products', function () {
    test('product exporter exports product successfully including all fields', function () {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'title' => 'Test Product for Export',
            'intro' => 'This is a test product intro.',
            'content' => '<p>This is test content.</p>',
            'sku' => 'PRD-001',
            'price' => 99.99,
            'discount_price' => 79.99,
            'is_discounted' => true,
            'stock_quantity' => 50,
            'website_title' => 'External Product Page',
            'website_url' => 'https://example.com/product',
            'created_by_user_id' => $user->id,
            'updated_by_user_id' => $user->id,
        ]);

        $exportedData = ProductExporter::test()->export($product);

        expect($exportedData[2])->toBe('Test Product for Export')
            ->and($exportedData[3])->toBe('This is a test product intro.')
            ->and($exportedData[4])->toContain('This is test content.')
            ->and($exportedData[5])->toBe('PRD-001')
            ->and($exportedData[6])->toBe('99.99')
            ->and($exportedData[7])->toBe('79.99')
            ->and($exportedData[8])->toBe('1')
            ->and($exportedData[9])->toBe('50')
            ->and($exportedData[10])->toBe('External Product Page')
            ->and($exportedData[11])->toBe('https://example.com/product');
    });

    test('product exporter handles null discount price', function () {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'title' => 'Product without Discount',
            'price' => 49.99,
            'discount_price' => null,
            'is_discounted' => false,
            'created_by_user_id' => $user->id,
            'updated_by_user_id' => $user->id,
        ]);

        $exportedData = ProductExporter::test()->export($product);

        expect($exportedData[6])->toBe('49.99')
            ->and($exportedData[7])->toBeNull()
            ->and($exportedData[8])->toBe('');
    });
});
