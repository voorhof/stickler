<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Media;
use App\Models\Page;
use App\Models\User;
use App\Observers\PageObserver;

use function Pest\Laravel\actingAs;

it('sets created_by_user_id and updated_by_user_id when a page is created', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    $page = Page::create([
        'title' => 'Observer Test Page',
        'intro' => 'Testing that the observer sets the creator and updater on create.',
    ]);

    expect($page->created_by_user_id)->toBe($user->id)
        ->and($page->updated_by_user_id)->toBe($user->id);
});

it('updates updated_by_user_id but not created_by_user_id when a page is updated', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $page = Page::create([
        'title' => 'Original Title',
        'intro' => 'Original intro text for the observer update test.',
    ]);

    expect($page->created_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $page->update(['title' => 'Updated Title']);

    expect($page->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($page->fresh()->updated_by_user_id)->toBe($updater->id);
});

it('does not overwrite user ids when no user is authenticated during update', function (): void {
    $creator = User::factory()->create();

    actingAs($creator);

    $page = Page::create([
        'title' => 'Auth Create No Auth Update',
        'intro' => 'Testing that unauthenticated updates do not overwrite the user ids.',
    ]);

    $originalCreatorId = $page->created_by_user_id;
    $originalUpdaterId = $page->updated_by_user_id;

    auth()->logout();

    $page->update(['title' => 'Updated Without Auth']);

    expect($page->fresh()->created_by_user_id)->toBe($originalCreatorId)
        ->and($page->fresh()->updated_by_user_id)->toBe($originalUpdaterId);
});

it('syncs image alt text to media name when a page is saved with rich content', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $uuid = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b';

    $page = Page::create([
        'title' => 'Page with Rich Content Image',
        'intro' => 'Intro text',
        'content' => '<p><img src="/storage/test.jpg" alt="Observer Test Alt" data-id="'.$uuid.'"></p>',
    ]);

    $media = Media::create([
        'uuid' => $uuid,
        'model_type' => $page->getMorphClass(),
        'model_id' => $page->getKey(),
        'collection_name' => 'content',
        'name' => 'original-name.jpg',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    expect($media->name)->toBe('original-name.jpg');

    // Trigger saved event by updating page
    $page->update([
        'title' => 'Updated Page Title',
    ]);

    expect($media->fresh()->name)->toBe('Observer Test Alt');
});

it('calls syncImageAltToMediaName in page observer saved method', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $uuid = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b';

    $page = Page::create([
        'title' => 'Page Observer Direct Test',
        'intro' => 'Intro text',
        'content' => '<p><img src="/storage/test.jpg" alt="Direct Observer Alt" data-id="'.$uuid.'"></p>',
    ]);

    $media = Media::create([
        'uuid' => $uuid,
        'model_type' => $page->getMorphClass(),
        'model_id' => $page->getKey(),
        'collection_name' => 'content',
        'name' => 'old-name.jpg',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $observer = new PageObserver;
    $observer->saved($page);

    expect($media->fresh()->name)->toBe('Direct Observer Alt');
});
