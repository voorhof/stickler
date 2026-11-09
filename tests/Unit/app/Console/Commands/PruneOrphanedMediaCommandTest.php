<?php

use App\Console\Commands\PruneOrphanedMediaCommand;
use App\Models\Media;
use App\Models\Page;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('runs with force flag and delegates to media-library:clean', function () {
    $this->artisan(PruneOrphanedMediaCommand::class, ['--force' => true])
        ->assertSuccessful();
});

it('asks for confirmation when force flag is not provided', function () {
    $this->artisan(PruneOrphanedMediaCommand::class)
        ->expectsQuestion('Are you sure you want to prune orphaned media files and records?', true)
        ->assertSuccessful();
});

it('cancels when user declines confirmation', function () {
    $this->artisan(PruneOrphanedMediaCommand::class)
        ->expectsQuestion('Are you sure you want to prune orphaned media files and records?', false)
        ->assertSuccessful();

    expect(Media::count())->toBe(0);
});

it('passes dry-run option to media-library:clean', function () {
    $this->artisan(PruneOrphanedMediaCommand::class, ['--force' => true, '--dry-run' => true])
        ->assertSuccessful();
});

it('deletes orphaned media records whose parent model no longer exists', function () {
    $user = User::factory()->create();
    actingAs($user);

    $media = Media::create([
        'model_type' => Page::class,
        'model_id' => 99999, // Non-existent page ID — simulates a true orphan
        'collection_name' => 'cover',
        'name' => 'Cover Image',
        'file_name' => 'test-image.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => config('media-library.disk_name'),
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $mediaId = $media->id;

    // The media record exists but points to a non-existent parent
    expect(Media::find($mediaId))->not->toBeNull();

    // Run the prune command
    $this->artisan(PruneOrphanedMediaCommand::class, ['--force' => true])
        ->assertSuccessful();

    // The orphaned media record should be deleted
    expect(Media::find($mediaId))->toBeNull();
});

it('does not delete media records that have an active parent model', function () {
    $user = User::factory()->create();
    actingAs($user);

    $page = Page::factory()->create();

    $media = Media::create([
        'model_type' => Page::class,
        'model_id' => $page->id,
        'collection_name' => 'cover',
        'name' => 'Cover Image',
        'file_name' => 'test-image.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => config('media-library.disk_name'),
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $mediaId = $media->id;

    // Run the prune command
    $this->artisan(PruneOrphanedMediaCommand::class, ['--force' => true])
        ->assertSuccessful();

    // The media record should still exist
    expect(Media::find($mediaId))->not->toBeNull()
        ->and(Media::find($mediaId)->model_id)->toBe($page->id);
});
