<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Models\Post;
use App\Models\User;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

test('it registers a preview conversion that crops to 400x400', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $post->registerAllMediaConversions();

    $preview = collect($post->mediaConversions)->first(fn ($c) => $c->getName() === 'preview');

    expect($preview)->not->toBeNull();

    $fitArgs = $preview->getManipulations()->getManipulationArgument('fit');

    expect($fitArgs)->not->toBeNull()
        ->and($fitArgs[0])->toBe(Fit::Crop)
        ->and($fitArgs[1])->toBe(400)
        ->and($fitArgs[2])->toBe(400);
});

test('it registers a thumb conversion that crops to 40x40', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $post->registerAllMediaConversions();

    $thumb = collect($post->mediaConversions)->first(fn ($c) => $c->getName() === 'thumb');

    expect($thumb)->not->toBeNull();

    $fitArgs = $thumb->getManipulations()->getManipulationArgument('fit');

    expect($fitArgs)->not->toBeNull()
        ->and($fitArgs[0])->toBe(Fit::Crop)
        ->and($fitArgs[1])->toBe(40)
        ->and($fitArgs[2])->toBe(40);
});

test('it queues preview, thumb, and webp conversions without deferring them', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $post->registerAllMediaConversions();

    $preview = collect($post->mediaConversions)->first(fn ($c) => $c->getName() === 'preview');
    $thumb = collect($post->mediaConversions)->first(fn ($c) => $c->getName() === 'thumb');
    $webp = collect($post->mediaConversions)->first(fn ($c) => $c->getName() === 'webp');

    expect($preview->shouldBeQueued())->toBeTrue()
        ->and($preview->shouldBeDeferred())->toBeFalse()
        ->and($thumb->shouldBeQueued())->toBeTrue()
        ->and($thumb->shouldBeDeferred())->toBeFalse()
        ->and($webp->shouldBeQueued())->toBeTrue()
        ->and($webp->shouldBeDeferred())->toBeFalse();
});

test('it sets webp format on preview and thumb conversions', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $post->registerAllMediaConversions();

    $preview = collect($post->mediaConversions)->first(fn ($c) => $c->getName() === 'preview');
    $thumb = collect($post->mediaConversions)->first(fn ($c) => $c->getName() === 'thumb');

    expect($preview->getManipulations()->getManipulationArgument('format')[0])->toBe('webp')
        ->and($thumb->getManipulations()->getManipulationArgument('format')[0])->toBe('webp');
});

test('it registers webp responsive conversion on matching collections', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $post->registerAllMediaConversions();

    $webpConversion = collect($post->mediaConversions)->first(fn ($c) => $c->getName() === 'webp');

    expect($webpConversion)->not->toBeNull()
        ->and($webpConversion->shouldGenerateResponsiveImages())->toBeTrue();
});

test('it applies webp responsive conversion only to intersecting collections', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $post->registerAllMediaConversions();

    $webpConversion = collect($post->mediaConversions)->first(fn ($c) => $c->getName() === 'webp');

    // Post's webpCollections returns ['cover', 'images', 'content']
    // Post passes ['cover', 'images', 'videos', 'documents', 'content']
    // Intersection should be ['cover', 'images', 'content']
    expect($webpConversion->getPerformOnCollections())->toHaveCount(3)
        ->and($webpConversion->getPerformOnCollections())->toContain('cover')
        ->and($webpConversion->getPerformOnCollections())->toContain('images')
        ->and($webpConversion->getPerformOnCollections())->toContain('content');
});

test('it registers webp conversion for user with intersecting collections', function () {
    $user = User::factory()->create();

    $user->registerAllMediaConversions();

    // User's webpCollections returns ['avatar', 'images']
    // User passes ['avatar', 'images', 'videos', 'documents']
    // Intersection should be ['avatar', 'images']
    $webpConversion = collect($user->mediaConversions)->first(fn ($c) => $c->getName() === 'webp');

    expect($webpConversion)->not->toBeNull()
        ->and($webpConversion->getPerformOnCollections())->toHaveCount(2)
        ->and($webpConversion->getPerformOnCollections())->toContain('avatar')
        ->and($webpConversion->getPerformOnCollections())->toContain('images');
});

test('it sets pdf page number and video frame extraction on preview and thumb', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $post->registerAllMediaConversions();

    $preview = collect($post->mediaConversions)->first(fn ($c) => $c->getName() === 'preview');
    $thumb = collect($post->mediaConversions)->first(fn ($c) => $c->getName() === 'thumb');

    expect($preview->getPdfPageNumber())->toBe(1)
        ->and($preview->getExtractVideoFrameAtSecond())->toBe(1.0)
        ->and($thumb->getPdfPageNumber())->toBe(1)
        ->and($thumb->getExtractVideoFrameAtSecond())->toBe(1.0);
});

test('it applies conversions to all specified collections', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $post->registerAllMediaConversions();

    $preview = collect($post->mediaConversions)->first(fn ($c) => $c->getName() === 'preview');

    expect($preview->getPerformOnCollections())->toHaveCount(5)
        ->and($preview->getPerformOnCollections())->toContain('cover')
        ->and($preview->getPerformOnCollections())->toContain('images')
        ->and($preview->getPerformOnCollections())->toContain('videos')
        ->and($preview->getPerformOnCollections())->toContain('documents')
        ->and($preview->getPerformOnCollections())->toContain('content');
});

test('getCoverMedia returns null when no cover media exists', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    expect($post->getCoverMedia())->toBeNull();
});

test('getCoverMedia returns the cover media item', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $media = Media::create([
        'model_type' => Post::class,
        'model_id' => $post->id,
        'collection_name' => 'cover',
        'name' => 'cover-image',
        'file_name' => 'cover-image.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'conversions_disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    expect($post->getCoverMedia())->not->toBeNull()
        ->and($post->getCoverMedia()->id)->toBe($media->id);
});

test('getCoverImageUrl returns null when no cover media exists', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    expect($post->getCoverImageUrl())->toBeNull();
});

test('getCoverImageUrl returns original url when webp conversion is not generated', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    Media::create([
        'model_type' => Post::class,
        'model_id' => $post->id,
        'collection_name' => 'cover',
        'name' => 'cover-image',
        'file_name' => 'cover-image.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'conversions_disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $url = $post->getCoverImageUrl();

    expect($url)->not->toBeNull()
        ->and(str_ends_with($url, 'cover-image.jpg'))->toBeTrue();
});

test('getCoverImageUrl returns webp url when conversion is generated', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    Media::create([
        'model_type' => Post::class,
        'model_id' => $post->id,
        'collection_name' => 'cover',
        'name' => 'cover-image',
        'file_name' => 'cover-image.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'conversions_disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => ['webp' => true],
        'responsive_images' => [],
    ]);

    $url = $post->getCoverImageUrl();

    expect($url)->not->toBeNull()
        ->and(str_contains($url, 'webp'))->toBeTrue();
});

test('getCoverImageUrl accepts custom conversions array', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    Media::create([
        'model_type' => Post::class,
        'model_id' => $post->id,
        'collection_name' => 'cover',
        'name' => 'cover-image',
        'file_name' => 'cover-image.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'conversions_disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $url = $post->getCoverImageUrl(['preview', '']);

    expect($url)->not->toBeNull();
});
