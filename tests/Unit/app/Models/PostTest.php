<?php

/** @noinspection PhpUndefinedFieldInspection */

use App\Models\Post;
use App\Models\User;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Activitylog\Support\LogOptions;

uses(RefreshDatabase::class);

test('a post can be created', function () {
    $user = User::factory()->create();

    $post = Post::forceCreate([
        'title' => 'My First Post',
        'intro' => 'This is the intro of my first post.',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($post->title)->toBe('My First Post')
        ->and($post->intro)->toBe('This is the intro of my first post.')
        ->and($post->slug)->toBe('my-first-post');
});

test('a post can be mass assigned fillable values', function () {
    $post = new Post([
        'slug' => 'mass-assigned-slug',
        'title' => 'Mass Assigned Post',
        'intro' => 'Some intro text here.',
        'content' => '<p>Some <strong>rich</strong> content.</p>',
        'order_column' => 5,
        'published_at' => now(),
    ]);

    expect($post->title)->toBe('Mass Assigned Post')
        ->and($post->slug)->toBe('mass-assigned-slug')
        ->and($post->intro)->toBe('Some intro text here.')
        ->and($post->content)->toBe('<p>Some <strong>rich</strong> content.</p>')
        ->and($post->order_column)->toBe(5);
});

test('it generates a slug from title', function () {
    $post = Post::forceCreate([
        'title' => 'My Blog Post Title',
        'intro' => 'Some intro text here.',
    ]);

    expect($post->slug)->toBe('my-blog-post-title');
});

test('it does not regenerate a slug when updating the title', function () {
    User::factory()->create();
    $post = Post::factory()->create(['title' => 'My Blog Post Title', 'slug' => 'my-blog-post-title']);
    $post->update(['title' => 'My Updated Blog Post Title']);

    expect($post->slug)->toBe('my-blog-post-title');
});

test('it generates unique slugs for posts with the same title', function () {
    User::factory()->create();
    $firstPost = Post::factory()->create(['title' => 'Duplicate Title', 'slug' => 'duplicate-title']);
    $secondPost = Post::factory()->create(['title' => 'Duplicate Title', 'slug' => 'duplicate-title-1']);

    expect($secondPost->slug)
        ->not->toBe($firstPost->slug)
        ->and(Str::startsWith($secondPost->slug, 'duplicate-title'))->toBeTrue();
});

test('it uses slug as route key name', function () {
    expect((new Post)->getRouteKeyName())->toBe('slug');
});

test('it has a default order_column of one', function () {
    $user = User::factory()->create();

    $post = Post::forceCreate([
        'title' => 'Order Column Test',
        'intro' => 'Testing the default order column.',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($post->order_column)->toBe(1);
});

test('it can be reordered', function () {
    User::factory()->create();
    $post1 = Post::factory()->create(['order_column' => 1]);
    $post2 = Post::factory()->create(['order_column' => 2]);

    Post::swapOrder($post1, $post2);

    expect($post1->fresh()->order_column)->toBe(2)
        ->and($post2->fresh()->order_column)->toBe(1);
});

test('it automatically reorders others when order_column is manually updated', function () {
    User::factory()->create();
    $post1 = Post::factory()->create(['order_column' => 1]);
    $post2 = Post::factory()->create(['order_column' => 2]);
    $post3 = Post::factory()->create(['order_column' => 3]);

    $post1->update(['order_column' => 3]);

    expect($post1->fresh()->order_column)->toBe(3)
        ->and($post2->fresh()->order_column)->toBe(1)
        ->and($post3->fresh()->order_column)->toBe(2);
});

test('it automatically reorders others when creating a new model with a specific order_column', function () {
    $user = User::factory()->create();
    $post1 = Post::factory()->create(['order_column' => 1]);
    $post2 = Post::factory()->create(['order_column' => 2]);
    $newPost = Post::forceCreate([
        'title' => 'Content Post',
        'intro' => 'Intro text.',
        'content' => '<p>This is <strong>rich</strong> content.</p>',
        'order_column' => 1,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($newPost->fresh()->order_column)->toBe(1)
        ->and($post1->fresh()->order_column)->toBe(2)
        ->and($post2->fresh()->order_column)->toBe(3);
});

test('it does reorder when creating a new model without specific order_column', function () {
    $user = User::factory()->create();
    $post1 = Post::factory()->create(['order_column' => 1]);
    $post2 = Post::factory()->create(['order_column' => 2]);

    $newPost = Post::forceCreate([
        'title' => 'Content Post',
        'intro' => 'Intro text.',
        'content' => '<p>This is <strong>rich</strong> content.</p>',
        // order_column will be 1 (because of SortableOnUpdate)
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($newPost->fresh()->order_column)->toBe(1)
        ->and($post1->fresh()->order_column)->toBe(2)
        ->and($post2->fresh()->order_column)->toBe(3);
});

test('it is not published when published_at is null', function () {
    User::factory()->create();
    $post = Post::factory()->create(['published_at' => null]);

    expect($post->published)->toBeFalse();
});

test('it is published when published_at is in the past', function () {
    User::factory()->create();
    $post = Post::factory()->create(['published_at' => now()->subDay()]);

    expect($post->published)->toBeTrue();
});

test('it can be soft deleted', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $post->delete();

    $this->assertSoftDeleted(Post::class, ['id' => $post->id]);
});

test('it can be restored after soft delete', function () {
    User::factory()->create();
    $post = Post::factory()->softDeleted()->create();

    $post->restore();

    $this->assertNotSoftDeleted(Post::class, ['id' => $post->id]);
});

test('it belongs to a creator user', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['created_by_user_id' => $user->id]);

    expect($post->creator)->toBeInstanceOf(User::class)
        ->and($post->creator->id)->toBe($user->id);
});

test('it belongs to an updater user', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['updated_by_user_id' => $user->id]);

    expect($post->updater)->toBeInstanceOf(User::class)
        ->and($post->updater->id)->toBe($user->id);
});

test('it returns a default user instance when creator is not loaded', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $user->forceDelete();

    // withDefault() ensures the relationship always returns a User instance
    expect($post->creator)->toBeInstanceOf(User::class)
        ->and($post->creator->name)->toBe('Guest User');
});

test('it returns a default user instance when updater is not loaded', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $user->forceDelete();

    // withDefault() ensures the relationship always returns a User instance
    expect($post->updater)->toBeInstanceOf(User::class)
        ->and($post->updater->name)->toBe('Guest User');
});

test('it returns rich content attributes', function () {
    $post = new Post;

    expect($post->hasRichContentAttribute('content'))->toBeTrue()
        ->and($post->hasRichContentAttribute('non_existent'))->toBeFalse();
});

test('it can attach and retrieve tags', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $post->attachTag('Laravel');

    expect($post->tags)->toHaveCount(1)
        ->and($post->tags->first()->name)->toBe('Laravel');
});

test('it casts is_published to a boolean and order_column to integer', function () {
    User::factory()->create();
    $post = Post::factory()->create([
        'published_at' => now(),
        'order_column' => '1',
    ]);

    expect($post->published)->toBeTrue()
        ->and($post->order_column)->toBe(1)->toBeInt();
});

test('it configures activity log options correctly', function () {
    $post = new Post;
    $options = $post->getActivitylogOptions();

    expect($options)->toBeInstanceOf(LogOptions::class);
});

test('content can be stored and retrieved', function () {
    $user = User::factory()->create();

    $post = Post::forceCreate([
        'title' => 'Content Post',
        'intro' => 'Intro text.',
        'content' => '<p>This is <strong>rich</strong> content.</p>',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($post->content)->toBe('<p>This is <strong>rich</strong> content.</p>');
});

test('content is nullable', function () {
    User::factory()->create();
    $post = Post::factory()->create(['content' => null]);

    expect($post->content)->toBeNull();
});

test('published_at is cast to a datetime instance', function () {
    User::factory()->create();
    $post = Post::factory()->create(['published_at' => '2024-01-15 12:00:00']);

    expect($post->published_at)->toBeInstanceOf(Carbon::class);
});

test('post implements has rich content contract', function () {
    $post = new Post;

    expect($post)->toBeInstanceOf(HasRichContent::class);
});

test('post uses interacts with rich content and sets up rich content attributes', function () {
    $post = new Post;

    expect($post->hasRichContentAttribute('content'))->toBeTrue()
        ->and($post->getRichContentAttribute('content'))->not->toBeNull();
});
