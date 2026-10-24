<?php

use App\Models\Page;
use App\Models\Post;
use App\Models\User;

test('it registers all standard media collections', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $post->registerMediaCollections();

    expect(array_keys($post->mediaCollections))->toHaveCount(6)
        ->and(array_keys($post->mediaCollections))->toContain('avatar')
        ->and(array_keys($post->mediaCollections))->toContain('cover')
        ->and(array_keys($post->mediaCollections))->toContain('images')
        ->and(array_keys($post->mediaCollections))->toContain('videos')
        ->and(array_keys($post->mediaCollections))->toContain('documents')
        ->and(array_keys($post->mediaCollections))->toContain('content');
});

test('it configures cover as a single file collection', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $post->registerMediaCollections();

    $cover = $post->mediaCollections['cover'];

    expect($cover->singleFile)->toBeTrue();
});

test('it configures images as a multi file collection', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $post->registerMediaCollections();

    $images = $post->mediaCollections['images'];

    expect($images->singleFile)->toBeFalse();
});

test('it enables responsive images on cover collection', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $post->registerMediaCollections();

    $cover = $post->mediaCollections['cover'];

    expect($cover->generateResponsiveImages)->toBeTrue();
});

test('it enables responsive images on images collection', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $post->registerMediaCollections();

    $images = $post->mediaCollections['images'];

    expect($images->generateResponsiveImages)->toBeTrue();
});

test('it enables responsive images on content collection', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $post->registerMediaCollections();

    $content = $post->mediaCollections['content'];

    expect($content->generateResponsiveImages)->toBeTrue();
});

test('it does not enable responsive images on videos collection', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $post->registerMediaCollections();

    $videos = $post->mediaCollections['videos'];

    expect($videos->generateResponsiveImages)->toBeFalse();
});

test('it does not enable responsive images on documents collection', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $post->registerMediaCollections();

    $documents = $post->mediaCollections['documents'];

    expect($documents->generateResponsiveImages)->toBeFalse();
});

test('it sets fallback url and path on cover collection', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $post->registerMediaCollections();

    $cover = $post->mediaCollections['cover'];

    expect($cover->fallbackUrls['default'])->toBe('/art/placeholders/pietje-200.png')
        ->and($cover->fallbackPaths['default'])->toBe(public_path('/art/placeholders/pietje-200.png'));
});

test('it sets fallback url on avatar collection using model name', function () {
    $user = User::factory()->create(['name' => 'John Doe']);

    $user->registerMediaCollections();

    $avatar = $user->mediaCollections['avatar'];

    expect($avatar->fallbackUrls['default'])->toContain('ui-avatars.com')
        ->and($avatar->fallbackUrls['default'])->toContain('J');
});

test('it uses configured disk for all collections', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $post->registerMediaCollections();

    $diskName = config('media-library.disk_name');

    foreach ($post->mediaCollections as $collection) {
        expect($collection->diskName)->toBe($diskName);
    }
});

test('it configures avatar as a single file collection', function () {
    $user = User::factory()->create();

    $user->registerMediaCollections();

    $avatar = $user->mediaCollections['avatar'];

    expect($avatar->singleFile)->toBeTrue();
});

test('it enables responsive images on avatar collection', function () {
    $user = User::factory()->create();

    $user->registerMediaCollections();

    $avatar = $user->mediaCollections['avatar'];

    expect($avatar->generateResponsiveImages)->toBeTrue();
});

test('it works across different model types', function () {
    User::factory()->create();
    $post = Post::factory()->create();
    $page = Page::factory()->create();
    $project = App\Models\Project::factory()->create();

    $post->registerMediaCollections();
    $page->registerMediaCollections();
    $project->registerMediaCollections();

    expect(array_keys($post->mediaCollections))->toBe(array_keys($page->mediaCollections))
        ->and(array_keys($page->mediaCollections))->toBe(array_keys($project->mediaCollections));
});
