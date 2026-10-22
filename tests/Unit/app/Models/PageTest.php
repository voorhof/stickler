<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

use App\Models\Page;
use App\Models\User;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Activitylog\Support\LogOptions;

test('a page can be created', function () {
    $user = User::factory()->create();

    $page = Page::forceCreate([
        'title' => 'My First Page',
        'intro' => 'This is the intro of my first page.',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($page->title)->toBe('My First Page')
        ->and($page->intro)->toBe('This is the intro of my first page.')
        ->and($page->slug)->toBe('my-first-page');
});

test('a page can be mass assigned fillable values', function () {
    $page = new Page([
        'slug' => 'mass-assigned-slug',
        'title' => 'Mass Assigned Page',
        'intro' => 'Some intro text here.',
        'content' => '<p>Some <strong>rich</strong> content.</p>',
        'order_column' => 5,
        'published_at' => now(),
    ]);

    expect($page->title)->toBe('Mass Assigned Page')
        ->and($page->slug)->toBe('mass-assigned-slug')
        ->and($page->intro)->toBe('Some intro text here.')
        ->and($page->content)->toBe('<p>Some <strong>rich</strong> content.</p>')
        ->and($page->order_column)->toBe(5);
});

test('it generates a slug from title', function () {
    $page = Page::forceCreate([
        'title' => 'My Blog Page Title',
        'intro' => 'Some intro text here.',
    ]);

    expect($page->slug)->toBe('my-blog-page-title');
});

test('it does not regenerate a slug when updating the title', function () {
    User::factory()->create();
    $page = Page::factory()->create(['title' => 'My Blog Page Title', 'slug' => 'my-blog-page-title']);
    $page->update(['title' => 'My Updated Blog Page Title']);

    expect($page->slug)->toBe('my-blog-page-title');
});

test('it generates unique slugs for pages with the same title', function () {
    User::factory()->create();
    $firstPage = Page::factory()->create(['title' => 'Duplicate Title', 'slug' => 'duplicate-title']);
    $secondPage = Page::factory()->create(['title' => 'Duplicate Title', 'slug' => 'duplicate-title-1']);

    expect($secondPage->slug)
        ->not->toBe($firstPage->slug)
        ->and(Str::startsWith($secondPage->slug, 'duplicate-title'))->toBeTrue();
});

test('it uses slug as route key name', function () {
    expect((new Page)->getRouteKeyName())->toBe('slug');
});

test('it has a default order_column of one', function () {
    $user = User::factory()->create();

    $page = Page::forceCreate([
        'title' => 'Order Column Test',
        'intro' => 'Testing the default order column.',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($page->order_column)->toBe(1);
});

test('it can be reordered', function () {
    User::factory()->create();
    $page1 = Page::factory()->create(['order_column' => 1]);
    $page2 = Page::factory()->create(['order_column' => 2]);

    Page::swapOrder($page1, $page2);

    expect($page1->fresh()->order_column)->toBe(2)
        ->and($page2->fresh()->order_column)->toBe(1);
});

test('it automatically reorders others when order_column is manually updated', function () {
    User::factory()->create();
    $page1 = Page::factory()->create(['order_column' => 1]);
    $page2 = Page::factory()->create(['order_column' => 2]);
    $page3 = Page::factory()->create(['order_column' => 3]);

    $page1->update(['order_column' => 3]);

    expect($page1->fresh()->order_column)->toBe(3)
        ->and($page2->fresh()->order_column)->toBe(1)
        ->and($page3->fresh()->order_column)->toBe(2);
});

test('it automatically reorders others when creating a new model with a specific order_column', function () {
    $user = User::factory()->create();
    $page1 = Page::factory()->create(['order_column' => 1]);
    $page2 = Page::factory()->create(['order_column' => 2]);
    $newPage = Page::forceCreate([
        'title' => 'Content Page',
        'intro' => 'Intro text.',
        'content' => '<p>This is <strong>rich</strong> content.</p>',
        'order_column' => 1,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($newPage->fresh()->order_column)->toBe(1)
        ->and($page1->fresh()->order_column)->toBe(2)
        ->and($page2->fresh()->order_column)->toBe(3);
});

test('it does reorder when creating a new model without specific order_column', function () {
    $user = User::factory()->create();
    $page1 = Page::factory()->create(['order_column' => 1]);
    $page2 = Page::factory()->create(['order_column' => 2]);

    $newPage = Page::forceCreate([
        'title' => 'Content Page',
        'intro' => 'Intro text.',
        'content' => '<p>This is <strong>rich</strong> content.</p>',
        // order_column will be 1 (because of SortableOnUpdate)
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($newPage->fresh()->order_column)->toBe(1)
        ->and($page1->fresh()->order_column)->toBe(2)
        ->and($page2->fresh()->order_column)->toBe(3);
});

test('it is not published when published_at is null', function () {
    User::factory()->create();
    $page = Page::factory()->create(['published_at' => null]);

    expect($page->published)->toBeFalse();
});

test('it is published when published_at is in the past', function () {
    User::factory()->create();
    $page = Page::factory()->create(['published_at' => now()->subDay()]);

    expect($page->published)->toBeTrue();
});

test('it can be soft deleted', function () {
    User::factory()->create();
    $page = Page::factory()->create();

    $page->delete();

    $this->assertSoftDeleted(Page::class, ['id' => $page->id]);
});

test('it can be restored after soft delete', function () {
    User::factory()->create();
    $page = Page::factory()->softDeleted()->create();

    $page->restore();

    $this->assertNotSoftDeleted(Page::class, ['id' => $page->id]);
});

test('it belongs to a creator user', function () {
    $user = User::factory()->create();
    $page = Page::factory()->create(['created_by_user_id' => $user->id]);

    expect($page->creator)->toBeInstanceOf(User::class)
        ->and($page->creator->id)->toBe($user->id);
});

test('it belongs to an updater user', function () {
    $user = User::factory()->create();
    $page = Page::factory()->create(['updated_by_user_id' => $user->id]);

    expect($page->updater)->toBeInstanceOf(User::class)
        ->and($page->updater->id)->toBe($user->id);
});

test('it returns a default user instance when creator is not loaded', function () {
    $user = User::factory()->create();
    $page = Page::factory()->create();

    $user->forceDelete();

    // withDefault() ensures the relationship always returns a User instance
    expect($page->creator)->toBeInstanceOf(User::class)
        ->and($page->creator->name)->toBe('Guest User');
});

test('it returns a default user instance when updater is not loaded', function () {
    $user = User::factory()->create();
    $page = Page::factory()->create();

    $user->forceDelete();

    // withDefault() ensures the relationship always returns a User instance
    expect($page->updater)->toBeInstanceOf(User::class)
        ->and($page->updater->name)->toBe('Guest User');
});

test('it returns rich content attributes', function () {
    $page = new Page;

    expect($page->hasRichContentAttribute('content'))->toBeTrue()
        ->and($page->hasRichContentAttribute('non_existent'))->toBeFalse();
});

test('it can attach and retrieve tags', function () {
    User::factory()->create();
    $page = Page::factory()->create();

    $page->attachTag('Laravel');

    expect($page->tags)->toHaveCount(1)
        ->and($page->tags->first()->name)->toBe('Laravel');
});

test('it casts is_published to a boolean and order_column to integer', function () {
    User::factory()->create();
    $page = Page::factory()->create([
        'published_at' => now(),
        'order_column' => '1',
    ]);

    expect($page->published)->toBeTrue()
        ->and($page->order_column)->toBe(1)->toBeInt();
});

test('it configures activity log options correctly', function () {
    $page = new Page;
    $options = $page->getActivitylogOptions();

    expect($options)->toBeInstanceOf(LogOptions::class);
});

test('content can be stored and retrieved', function () {
    $user = User::factory()->create();

    $page = Page::forceCreate([
        'title' => 'Content Page',
        'intro' => 'Intro text.',
        'content' => '<p>This is <strong>rich</strong> content.</p>',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($page->content)->toBe('<p>This is <strong>rich</strong> content.</p>');
});

test('content is nullable', function () {
    User::factory()->create();
    $page = Page::factory()->create(['content' => null]);

    expect($page->content)->toBeNull();
});

test('published_at is cast to a datetime instance', function () {
    User::factory()->create();
    $page = Page::factory()->create(['published_at' => '2024-01-15 12:00:00']);

    expect($page->published_at)->toBeInstanceOf(Carbon::class);
});

test('page implements has rich content contract', function () {
    $page = new Page;

    expect($page)->toBeInstanceOf(HasRichContent::class);
});

test('page uses interacts with rich content and sets up rich content attributes', function () {
    $page = new Page;

    expect($page->hasRichContentAttribute('content'))->toBeTrue()
        ->and($page->getRichContentAttribute('content'))->not->toBeNull();
});

test('it registers standard media collections', function () {
    User::factory()->create();
    $page = Page::factory()->create();

    $page->registerMediaCollections();

    expect(array_keys($page->mediaCollections))->toContain('cover')
        ->and(array_keys($page->mediaCollections))->toContain('images')
        ->and(array_keys($page->mediaCollections))->toContain('videos')
        ->and(array_keys($page->mediaCollections))->toContain('documents')
        ->and(array_keys($page->mediaCollections))->toContain('content');
});

test('it registers standard media conversions', function () {
    User::factory()->create();
    $page = Page::factory()->create();

    $page->registerAllMediaConversions();

    $names = collect($page->mediaConversions)->map(fn ($c) => $c->getName())->toArray();

    expect($names)->toContain('preview')
        ->and($names)->toContain('thumb')
        ->and($names)->toContain('webp');
});
