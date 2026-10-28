<?php

use App\Http\Controllers\Public\ProductController;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantOption;
use App\Models\ProductVariantOptionType;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Laravel\Head\Facades\Head;

describe('products index', function () {
    test('it displays published products in index ordered by order_column', function () {
        User::factory()->create();

        $publishedProduct1 = Product::factory()->create([
            'title' => 'First Order Product',
            'published_at' => now()->subDays(2),
        ]);

        $publishedProduct2 = Product::factory()->create([
            'title' => 'Second Order Product',
            'published_at' => now()->subDay(),
        ]);

        $futureProduct = Product::factory()->create([
            'title' => 'Future Product',
            'published_at' => now()->addDay(),
        ]);

        $unpublishedProduct = Product::factory()->create([
            'title' => 'Unpublished Product',
            'published_at' => null,
        ]);

        $publishedProduct2->updateQuietly(['order_column' => 1]);
        $publishedProduct1->updateQuietly(['order_column' => 2]);

        $response = $this->get(route('products.index'));

        $response->assertSuccessful()
            ->assertViewIs('public.products.index')
            ->assertViewHas('products', function (LengthAwarePaginator $products) use ($publishedProduct1, $publishedProduct2, $futureProduct, $unpublishedProduct) {
                expect($products->pluck('id')->all())->toBe([$publishedProduct2->id, $publishedProduct1->id])
                    ->and($products->contains($futureProduct))->toBeFalse()
                    ->and($products->contains($unpublishedProduct))->toBeFalse()
                    ->and($products->first()->relationLoaded('media'))->toBeTrue()
                    ->and($products->first()->relationLoaded('categories'))->toBeTrue()
                    ->and($products->perPage())->toBe(6);

                return true;
            })
            ->assertSeeText($publishedProduct1->title)
            ->assertSeeText($publishedProduct2->title)
            ->assertDontSeeText($futureProduct->title)
            ->assertDontSeeText($unpublishedProduct->title);
    });

    test('it sets head metadata on index', function () {
        $this->get(route('products.index'));

        $html = Head::toHtml();

        expect($html)->toContain('<title>'.config('app.name').' | '.e(__('Products')).'</title>')
            ->and($html)->toContain(e(__('Products')).' '.config('app.name'));
    });
});

describe('products show', function () {
    test('it displays published product in show route', function () {
        User::factory()->create();

        $product = Product::factory()->create([
            'title' => 'Test Product',
            'intro' => 'Welcome to our test product',
            'content' => 'Full content of test product',
            'published_at' => now()->subHour(),
        ]);

        $response = $this->get(route('products.show', $product));

        $response->assertSuccessful()
            ->assertViewIs('public.products.show')
            ->assertViewHas('product', function (Product $viewProduct) use ($product) {
                return $viewProduct->id === $product->id;
            })
            ->assertSeeText($product->title)
            ->assertSeeText($product->intro);

        $html = Head::toHtml();

        expect($html)->toContain('<title>'.config('app.name').' | '.$product->title.'</title>')
            ->and($html)->toContain($product->intro);
    });

    test('it returns 404 when showing a product published in the future', function () {
        User::factory()->create();

        $futureProduct = Product::factory()->create([
            'title' => 'Coming Soon',
            'published_at' => Carbon::now()->addDays(5),
        ]);

        $response = $this->get(route('products.show', $futureProduct));

        $response->assertNotFound();
    });

    test('it returns 404 when showing an unpublished product with null published_at', function () {
        User::factory()->create();

        $unpublishedProduct = Product::factory()->create([
            'title' => 'Draft Product',
            'published_at' => null,
        ]);

        $response = $this->get(route('products.show', $unpublishedProduct));

        $response->assertNotFound();
    });

    test('it returns 404 when showing a nonexistent product', function () {
        $response = $this->get(route('products.show', 'non-existent-product-slug'));

        $response->assertNotFound();
    });

    test('it displays product with variants and their options', function () {
        User::factory()->create();

        $colorType = ProductVariantOptionType::factory()->create(['name' => 'Color']);
        $redOption = ProductVariantOption::factory()->create([
            'product_variant_option_type_id' => $colorType->id,
            'name' => 'Red',
            'value' => '#FF0000',
        ]);

        $product = Product::factory()->create([
            'published_at' => now()->subHour(),
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'TSHIRT-RED-M',
            'price' => 2999,
            'stock_quantity' => 10,
        ]);
        $variant->options()->attach($redOption);

        $response = $this->get(route('products.show', $product));

        $response->assertSuccessful()
            ->assertViewIs('public.products.show')
            ->assertViewHas('product', function (Product $viewProduct) use ($product, $variant, $redOption) {
                expect($viewProduct->id)->toBe($product->id)
                    ->and($viewProduct->relationLoaded('variants'))->toBeTrue()
                    ->and($viewProduct->relationLoaded('categories'))->toBeTrue();

                $viewVariant = $viewProduct->variants->firstWhere('id', $variant->id);

                expect($viewVariant)->not->toBeNull()
                    ->and($viewVariant->relationLoaded('options'))->toBeTrue()
                    ->and($viewVariant->options->contains($redOption))->toBeTrue();

                return true;
            })
            ->assertSeeText(__('Variants'))
            ->assertSeeText('Red')
            ->assertSeeText('TSHIRT-RED-M');
    });

    test('it displays product with categories', function () {
        User::factory()->create();

        $categories = Category::factory()->count(2)->create(['for_products' => true]);

        $product = Product::factory()->create([
            'published_at' => now()->subHour(),
        ]);
        $product->categories()->sync($categories->pluck('id'));

        $response = $this->get(route('products.show', $product));

        $response->assertSuccessful()
            ->assertViewIs('public.products.show')
            ->assertViewHas('product', function (Product $viewProduct) use ($product, $categories) {
                expect($viewProduct->id)->toBe($product->id)
                    ->and($viewProduct->relationLoaded('categories'))->toBeTrue()
                    ->and($viewProduct->categories->pluck('id')->sort()->values()->toArray())
                    ->toBe($categories->pluck('id')->sort()->values()->toArray());

                return true;
            })
            ->assertSeeText($categories[0]->title)
            ->assertSeeText($categories[1]->title);
    });
});

describe('controller methods', function () {
    test('direct controller index method returns correct view and data', function () {
        User::factory()->create();

        $product = Product::factory()->create([
            'published_at' => now()->subDay(),
        ]);

        $controller = new ProductController;
        $view = $controller->index();

        expect($view->name())->toBe('public.products.index')
            ->and($view->getData())->toHaveKey('products')
            ->and($view->getData()['products'])->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($view->getData()['products']->pluck('id')->all())->toContain($product->id);
    });

    test('direct controller show method returns view for published product', function () {
        User::factory()->create();

        $publishedProduct = Product::factory()->create([
            'published_at' => now()->subDay(),
        ]);

        $controller = new ProductController;
        $view = $controller->show($publishedProduct);

        expect($view->name())->toBe('public.products.show')
            ->and($view->getData()['product']->id)->toBe($publishedProduct->id);

    });
});
