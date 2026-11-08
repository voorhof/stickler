<?php

use App\Actions\Sluggable\GenerateSlugAction;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\Project;

test('it determines excluded slugs case-insensitively', function () {
    $action = new GenerateSlugAction;

    expect($action->isExcludedSlug('cart'))->toBeTrue()
        ->and($action->isExcludedSlug('CART'))->toBeTrue()
        ->and($action->isExcludedSlug('Admin'))->toBeTrue()
        ->and($action->isExcludedSlug('checkout'))->toBeTrue()
        ->and($action->isExcludedSlug('random-allowed-slug'))->toBeFalse();
});

test('it generates numeric suffixes for Page model when matching reserved words', function () {
    $page1 = Page::create([
        'title' => 'Cart',
        'intro' => 'First cart page',
    ]);

    $page2 = Page::create([
        'title' => 'Cart',
        'intro' => 'Second cart page',
    ]);

    expect($page1->slug)->toBe('cart-1')
        ->and($page2->slug)->toBe('cart-2');
});

test('it protects Post, Product, Category and Project models from reserved slugs', function () {
    $post = Post::create([
        'title' => 'Admin',
        'intro' => 'Post about admin',
    ]);

    $product = Product::create([
        'title' => 'Checkout',
        'intro' => 'Product named checkout',
        'price' => 19.99,
        'stock_quantity' => 10,
    ]);

    $category = Category::create([
        'title' => 'Contact',
    ]);

    $project = Project::create([
        'title' => 'Brandbook',
        'intro' => 'Project named brandbook',
    ]);

    expect($post->slug)->toBe('admin-1')
        ->and($product->slug)->toBe('checkout-1')
        ->and($category->slug)->toBe('contact-1')
        ->and($project->slug)->toBe('brandbook-1');
});

test('it suffixes explicitly assigned reserved slugs on model creation and update', function () {
    $post = Post::create([
        'title' => 'Sample Post',
        'slug' => 'cart',
        'intro' => 'Intro',
    ]);

    expect($post->slug)->toBe('cart-1');

    $post->update(['slug' => 'checkout']);

    expect($post->fresh()->slug)->toBe('checkout-1');
});

test('it leaves non-reserved slugs untouched', function () {
    $page = Page::create([
        'title' => 'Company About Us',
        'intro' => 'About us intro',
    ]);

    expect($page->slug)->toBe('company-about-us');
});

test('it generates valid regex pattern excluding reserved slugs', function () {
    $pattern = GenerateSlugAction::getExcludedSlugsRegexPattern();

    expect(preg_match("#$pattern#", 'cart'))->toBe(0)
        ->and(preg_match("#$pattern#", 'admin'))->toBe(0)
        ->and(preg_match("#$pattern#", 'checkout'))->toBe(0)
        ->and(preg_match("#$pattern#", 'contact'))->toBe(0)
        ->and(preg_match("#$pattern#", 'cart-1'))->toBe(1)
        ->and(preg_match("#$pattern#", 'admin-2'))->toBe(1)
        ->and(preg_match("#$pattern#", 'about-us'))->toBe(1)
        ->and(preg_match("#$pattern#", 'pricing'))->toBe(1);
});
