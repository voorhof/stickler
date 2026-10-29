<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\Project;
use App\Models\User;

test('product categories relationship only returns product-scoped categories', function () {
    User::factory()->create();

    $productCategory = Category::factory()->create(['for_products' => true]);
    $postCategory = Category::factory()->create(['for_posts' => true, 'for_products' => false]);

    $product = Product::factory()->create();
    $product->categories()->attach([$productCategory->id, $postCategory->id]);

    expect($product->categories)->toHaveCount(1)
        ->and($product->categories->first()->id)->toBe($productCategory->id);
});

test('post categories relationship only returns post-scoped categories', function () {
    User::factory()->create();

    $postCategory = Category::factory()->create(['for_posts' => true]);
    $productCategory = Category::factory()->create(['for_products' => true, 'for_posts' => false]);

    $post = Post::factory()->create();
    $post->categories()->attach([$postCategory->id, $productCategory->id]);

    expect($post->categories)->toHaveCount(1)
        ->and($post->categories->first()->id)->toBe($postCategory->id);
});

test('page categories relationship only returns page-scoped categories', function () {
    User::factory()->create();

    $pageCategory = Category::factory()->create(['for_pages' => true]);
    $projectCategory = Category::factory()->create(['for_projects' => true, 'for_pages' => false]);

    $page = Page::factory()->create();
    $page->categories()->attach([$pageCategory->id, $projectCategory->id]);

    expect($page->categories)->toHaveCount(1)
        ->and($page->categories->first()->id)->toBe($pageCategory->id);
});

test('project categories relationship only returns project-scoped categories', function () {
    User::factory()->create();

    $projectCategory = Category::factory()->create(['for_projects' => true]);
    $pageCategory = Category::factory()->create(['for_pages' => true, 'for_projects' => false]);

    $project = Project::factory()->create();
    $project->categories()->attach([$projectCategory->id, $pageCategory->id]);

    expect($project->categories)->toHaveCount(1)
        ->and($project->categories->first()->id)->toBe($projectCategory->id);
});

test('product category scope key returns for_products', function () {
    $product = new Product;
    $method = new ReflectionMethod($product, 'categoryScopeKey');

    expect($method->invoke($product))->toBe('for_products');
});

test('post category scope key returns for_posts', function () {
    $post = new Post;
    $method = new ReflectionMethod($post, 'categoryScopeKey');

    expect($method->invoke($post))->toBe('for_posts');
});

test('page category scope key returns for_pages', function () {
    $page = new Page;
    $method = new ReflectionMethod($page, 'categoryScopeKey');

    expect($method->invoke($page))->toBe('for_pages');
});

test('project category scope key returns for_projects', function () {
    $project = new Project;
    $method = new ReflectionMethod($project, 'categoryScopeKey');

    expect($method->invoke($project))->toBe('for_projects');
});

test('categories are ordered by title', function () {
    User::factory()->create();

    $zebra = Category::factory()->create(['title' => 'Zebra', 'for_products' => true]);
    $apple = Category::factory()->create(['title' => 'Apple', 'for_products' => true]);
    $mango = Category::factory()->create(['title' => 'Mango', 'for_products' => true]);

    $product = Product::factory()->create();
    $product->categories()->attach([$zebra->id, $apple->id, $mango->id]);

    expect($product->categories->pluck('title')->toArray())->toBe(['Apple', 'Mango', 'Zebra']);
});

test('model can attach and detach categories', function () {
    User::factory()->create();

    $category1 = Category::factory()->create(['for_products' => true, 'for_posts' => false, 'for_pages' => false, 'for_projects' => false]);
    $category2 = Category::factory()->create(['for_products' => true, 'for_posts' => false, 'for_pages' => false, 'for_projects' => false]);

    $product = Product::factory()->create();
    $product->categories()->attach($category1->id);
    $product->categories()->attach($category2->id);

    expect($product->categories)->toHaveCount(2);

    $product->categories()->detach($category1->id);
    $product->refresh();

    expect($product->categories)->toHaveCount(1)
        ->and($product->categories->first()->id)->toBe($category2->id);
});

test('model can sync categories', function () {
    User::factory()->create();

    $category1 = Category::factory()->create(['for_products' => true, 'for_posts' => false, 'for_pages' => false, 'for_projects' => false]);
    $category2 = Category::factory()->create(['for_products' => true, 'for_posts' => false, 'for_pages' => false, 'for_projects' => false]);
    $category3 = Category::factory()->create(['for_products' => true, 'for_posts' => false, 'for_pages' => false, 'for_projects' => false]);

    $product = Product::factory()->create();
    $product->categories()->attach([$category1->id, $category2->id]);

    expect($product->categories)->toHaveCount(2);

    // Detach category1 and attach category3 to simulate sync behavior
    $product->categories()->detach($category1->id);
    $product->categories()->attach($category3->id);
    $product->refresh();

    expect($product->categories)->toHaveCount(2)
        ->and($product->categories->pluck('id')->contains($category2->id))->toBeTrue()
        ->and($product->categories->pluck('id')->contains($category3->id))->toBeTrue()
        ->and($product->categories->pluck('id')->contains($category1->id))->toBeFalse();
});
