<?php

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a tag can be created', function () {
    $user = User::factory()->create();

    $tag = Tag::forceCreate([
        'name' => 'My Tag',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($tag->name)->toBe('My Tag')
        ->and($tag->slug)->toBe('my-tag');
});

test('a tag can be mass assigned fillable values', function () {
    $tag = new Tag([
        'url_slug' => 'mass-assigned-slug',
        'name' => 'Mass Assigned Name',
        'type' => 'Mass Assigned Type',
        'order_column' => 3,
    ]);

    expect($tag->name)->toBe('Mass Assigned Name')
        ->and($tag->url_slug)->toBe('mass-assigned-slug')
        ->and($tag->type)->toBe('Mass Assigned Type')
        ->and($tag->order_column)->toBe(3);
});

test('it generates a url_slug from name', function () {
    $tag = Tag::forceCreate([
        'name' => 'My Tag Name',
    ]);

    expect($tag->url_slug)->toBe('my-tag-name');
});

test('it does not regenerate a url_slug when updating the name', function () {
    User::factory()->create();
    $tag = Tag::factory()->create(['name' => 'My Tag Name', 'url_slug' => 'my-tag-name']);
    $tag->update(['name' => 'My Updated Tag Name']);

    expect($tag->url_slug)->toBe('my-tag-name');
});

test('it uses url_slug as route key name', function () {
    expect((new Tag)->getRouteKeyName())->toBe('url_slug');
});

test('it has a default order_column of one', function () {
    $user = User::factory()->create();

    $tag = Tag::forceCreate([
        'name' => 'Order Column Test',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($tag->order_column)->toBe(1);
});

test('it can be reordered', function () {
    User::factory()->create();
    $tag1 = Tag::factory()->create(['order_column' => 1]);
    $tag2 = Tag::factory()->create(['order_column' => 2]);

    Tag::swapOrder($tag1, $tag2);

    expect($tag1->fresh()->order_column)->toBe(2)
        ->and($tag2->fresh()->order_column)->toBe(1);
});

test('it automatically reorders others when order_column is manually updated', function () {
    User::factory()->create();
    $tag1 = Tag::factory()->create(['order_column' => 1]);
    $tag2 = Tag::factory()->create(['order_column' => 2]);
    $tag3 = Tag::factory()->create(['order_column' => 3]);

    $tag1->update(['order_column' => 3]);

    expect($tag1->fresh()->order_column)->toBe(3)
        ->and($tag2->fresh()->order_column)->toBe(1)
        ->and($tag3->fresh()->order_column)->toBe(2);
});

test('it automatically reorders others when creating a new model with a specific order_column', function () {
    $user = User::factory()->create();
    $tag1 = Tag::factory()->create(['order_column' => 1]);
    $tag2 = Tag::factory()->create(['order_column' => 2]);

    $newTag = Tag::forceCreate([
        'name' => 'Order Column Test',
        'order_column' => 1,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($newTag->fresh()->order_column)->toBe(1)
        ->and($tag1->fresh()->order_column)->toBe(2)
        ->and($tag2->fresh()->order_column)->toBe(3);
});

test('it does not reorder when creating a new model without specific order_column', function () {
    $user = User::factory()->create();
    $tag1 = Tag::factory()->create(['order_column' => 1]);
    $tag2 = Tag::factory()->create(['order_column' => 2]);

    $newTag = Tag::forceCreate([
        'name' => 'Order Column Test',
        // order_column will be 1 (because of SortableOnUpdate)
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($newTag->fresh()->order_column)->toBe(1)
        ->and($tag1->fresh()->order_column)->toBe(2)
        ->and($tag2->fresh()->order_column)->toBe(3);
});

test('it can be soft deleted', function () {
    User::factory()->create();
    $tag = Tag::factory()->create();

    $tag->delete();

    $this->assertSoftDeleted(Tag::class, ['id' => $tag->id]);
});

test('it can be restored after soft delete', function () {
    User::factory()->create();
    $tag = Tag::factory()->softDeleted()->create();

    $tag->restore();

    $this->assertNotSoftDeleted(Tag::class, ['id' => $tag->id]);
});

test('it belongs to a creator user', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->create(['created_by_user_id' => $user->id]);

    expect($tag->creator)->toBeInstanceOf(User::class)
        ->and($tag->creator->id)->toBe($user->id);
});

test('it belongs to an updater user', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->create(['updated_by_user_id' => $user->id]);

    expect($tag->updater)->toBeInstanceOf(User::class)
        ->and($tag->updater->id)->toBe($user->id);
});

test('it returns a default user instance when creator is not loaded', function () {
    $tag = new Tag;

    // withDefault() ensures the relationship always returns a User instance
    expect($tag->creator)->toBeInstanceOf(User::class)
        ->and($tag->creator->name)->toBe('Guest User');
});

test('it returns a default user instance when updater is not loaded', function () {
    $tag = new Tag;

    // withDefault() ensures the relationship always returns a User instance
    expect($tag->updater)->toBeInstanceOf(User::class)
        ->and($tag->updater->name)->toBe('Guest User');
});

test('it returns name as title attribute', function () {
    User::factory()->create();
    $tag = Tag::factory()->create(['name' => 'News Tag']);

    expect($tag->title)->toBe('News Tag');
});

test('it returns default locale', function () {
    $tag = new Tag;

    expect($tag->getLocale())->toBe('nl_BE');
});

test('it returns default fallback locale', function () {
    $tag = new Tag;

    expect($tag->getFallbackLocale())->toBe('en_US');
});

test('it has media relationship', function () {
    $tag = new Tag;

    expect($tag->media())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\MorphToMany::class);
});

test('it has posts relationship', function () {
    $tag = new Tag;

    expect($tag->posts())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\MorphToMany::class);
});

test('it has projects relationship', function () {
    $tag = new Tag;

    expect($tag->projects())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\MorphToMany::class);
});

test('it generates unique url_slug for tags with same name', function () {
    $tag1 = Tag::forceCreate(['name' => 'General Tag']);
    $tag2 = Tag::forceCreate(['name' => 'General Tag']);

    expect($tag1->url_slug)->toBe('general-tag')
        ->and($tag2->url_slug)->toBe('general-tag-1');
});

test('it configures activity log options correctly', function () {
    $tag = new Tag;
    $options = $tag->getActivitylogOptions();

    expect($options)->toBeInstanceOf(Spatie\Activitylog\Support\LogOptions::class);
});
