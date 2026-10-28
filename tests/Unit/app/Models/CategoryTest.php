<?php

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use App\Policies\CategoryPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Support\Str;
use Spatie\Activitylog\Support\LogOptions;

test('a category can be created', function () {
    $user = User::factory()->create();

    $category = Category::forceCreate([
        'title' => 'Electronics',
        'slug' => 'electronics',
        'for_pages' => false,
        'for_posts' => true,
        'for_products' => true,
        'for_projects' => false,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($category->title)->toBe('Electronics')
        ->and($category->slug)->toBe('electronics')
        ->and($category->for_posts)->toBeTrue()
        ->and($category->for_products)->toBeTrue()
        ->and($category->for_pages)->toBeFalse()
        ->and($category->for_projects)->toBeFalse();
});

test('a category can be mass assigned fillable values', function () {
    $category = new Category([
        'slug' => 'mass-assigned-slug',
        'title' => 'Mass Assigned Category',
        'for_pages' => true,
        'for_posts' => false,
        'for_products' => true,
        'for_projects' => true,
        'order_column' => 5,
    ]);

    expect($category->title)->toBe('Mass Assigned Category')
        ->and($category->slug)->toBe('mass-assigned-slug')
        ->and($category->for_pages)->toBeTrue()
        ->and($category->for_posts)->toBeFalse()
        ->and($category->for_products)->toBeTrue()
        ->and($category->for_projects)->toBeTrue()
        ->and($category->order_column)->toBe(5);
});

test('it generates a slug from title', function () {
    $category = Category::forceCreate([
        'title' => 'My Category Title',
    ]);

    expect($category->slug)->toBe('my-category-title');
});

test('it does not regenerate a slug when updating the title', function () {
    User::factory()->create();
    $category = Category::factory()->create(['title' => 'My Category Title', 'slug' => 'my-category-title']);
    $category->update(['title' => 'My Updated Category Title']);

    expect($category->slug)->toBe('my-category-title');
});

test('it generates unique slugs for categories with the same title', function () {
    User::factory()->create();
    $firstCategory = Category::factory()->create(['title' => 'Duplicate Title', 'slug' => 'duplicate-title']);
    $secondCategory = Category::factory()->create(['title' => 'Duplicate Title 2', 'slug' => 'duplicate-title-1']);

    expect($secondCategory->slug)
        ->not->toBe($firstCategory->slug)
        ->and(Str::startsWith($secondCategory->slug, 'duplicate-title'))->toBeTrue();
});

test('it uses slug as route key name', function () {
    expect((new Category)->getRouteKeyName())->toBe('slug');
});

test('it has a default order_column of one', function () {
    $user = User::factory()->create();

    $category = Category::forceCreate([
        'title' => 'Order Column Test',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($category->order_column)->toBe(1);
});

test('it can be reordered', function () {
    User::factory()->create();
    $category1 = Category::factory()->create(['order_column' => 1]);
    $category2 = Category::factory()->create(['order_column' => 2]);

    Category::swapOrder($category1, $category2);

    expect($category1->fresh()->order_column)->toBe(2)
        ->and($category2->fresh()->order_column)->toBe(1);
});

test('it automatically reorders others when order_column is manually updated', function () {
    User::factory()->create();
    $category1 = Category::factory()->create(['order_column' => 1]);
    $category2 = Category::factory()->create(['order_column' => 2]);
    $category3 = Category::factory()->create(['order_column' => 3]);

    $category1->update(['order_column' => 3]);

    expect($category1->fresh()->order_column)->toBe(3)
        ->and($category2->fresh()->order_column)->toBe(1)
        ->and($category3->fresh()->order_column)->toBe(2);
});

test('it can be soft deleted', function () {
    User::factory()->create();
    $category = Category::factory()->create();

    $category->delete();

    $this->assertSoftDeleted(Category::class, ['id' => $category->id]);
});

test('it can be restored after soft delete', function () {
    User::factory()->create();
    $category = Category::factory()->softDeleted()->create();

    $category->restore();

    $this->assertNotSoftDeleted(Category::class, ['id' => $category->id]);
});

test('it belongs to a creator user', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['created_by_user_id' => $user->id]);

    expect($category->creator)->toBeInstanceOf(User::class)
        ->and($category->creator->id)->toBe($user->id);
});

test('it belongs to an updater user', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['updated_by_user_id' => $user->id]);

    expect($category->updater)->toBeInstanceOf(User::class)
        ->and($category->updater->id)->toBe($user->id);
});

test('it returns a default user instance when creator is not loaded', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $user->forceDelete();

    expect($category->creator)->toBeInstanceOf(User::class)
        ->and($category->creator->name)->toBe('Guest User');
});

test('it returns a default user instance when updater is not loaded', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $user->forceDelete();

    expect($category->updater)->toBeInstanceOf(User::class)
        ->and($category->updater->name)->toBe('Guest User');
});

test('it casts scope flags to booleans', function () {
    User::factory()->create();
    $category = Category::factory()->create([
        'for_pages' => 1,
        'for_posts' => 0,
        'for_products' => 1,
        'for_projects' => 0,
    ]);

    expect($category->for_pages)->toBeTrue()->toBeBool()
        ->and($category->for_posts)->toBeFalse()->toBeBool()
        ->and($category->for_products)->toBeTrue()->toBeBool()
        ->and($category->for_projects)->toBeFalse()->toBeBool();
});

test('it has many pages', function () {
    User::factory()->create();
    $category = Category::factory()->create(['for_pages' => true]);
    $pages = Page::factory()->count(3)->create();

    $category->pages()->attach($pages->pluck('id'));

    expect($category->pages)->toHaveCount(3)
        ->and($category->pages->pluck('id')->sort()->values()->toArray())
        ->toBe($pages->pluck('id')->sort()->values()->toArray());
});

test('it has many posts', function () {
    User::factory()->create();
    $category = Category::factory()->create(['for_posts' => true]);
    $posts = Post::factory()->count(3)->create();

    $category->posts()->attach($posts->pluck('id'));

    expect($category->posts)->toHaveCount(3)
        ->and($category->posts->pluck('id')->sort()->values()->toArray())
        ->toBe($posts->pluck('id')->sort()->values()->toArray());
});

test('it has many products', function () {
    User::factory()->create();
    $category = Category::factory()->create(['for_products' => true]);
    $products = Product::factory()->count(3)->create();

    $category->products()->attach($products->pluck('id'));

    expect($category->products)->toHaveCount(3)
        ->and($category->products->pluck('id')->sort()->values()->toArray())
        ->toBe($products->pluck('id')->sort()->values()->toArray());
});

test('it has many projects', function () {
    User::factory()->create();
    $category = Category::factory()->create(['for_projects' => true]);
    $projects = Project::factory()->count(3)->create();

    $category->projects()->attach($projects->pluck('id'));

    expect($category->projects)->toHaveCount(3)
        ->and($category->projects->pluck('id')->sort()->values()->toArray())
        ->toBe($projects->pluck('id')->sort()->values()->toArray());
});

test('it configures activity log options correctly', function () {
    $category = new Category;
    $options = $category->getActivitylogOptions();

    expect($options)->toBeInstanceOf(LogOptions::class);
});

test('category model has category policy attribute configured', function () {
    $attributes = new ReflectionClass(Category::class)->getAttributes(UsePolicy::class);

    expect($attributes)->toHaveCount(1)
        ->and($attributes[0]->getArguments()[0])->toBe(CategoryPolicy::class);
});
