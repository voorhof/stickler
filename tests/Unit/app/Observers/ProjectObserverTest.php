<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('sets created_by_user_id and updated_by_user_id when a project is created', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    $project = Project::create([
        'title' => 'Observer Test Project',
        'intro' => 'Testing that the observer sets the creator and updater on create.',
    ]);

    expect($project->created_by_user_id)->toBe($user->id)
        ->and($project->updated_by_user_id)->toBe($user->id);
});

it('updates updated_by_user_id but not created_by_user_id when a project is updated', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $project = Project::create([
        'title' => 'Original Title',
        'intro' => 'Original intro text for the observer update test.',
    ]);

    expect($project->created_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $project->update(['title' => 'Updated Title']);

    expect($project->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($project->fresh()->updated_by_user_id)->toBe($updater->id);
});

it('does not overwrite user ids when no user is authenticated during update', function (): void {
    $creator = User::factory()->create();

    actingAs($creator);

    $project = Project::create([
        'title' => 'Auth Create No Auth Update',
        'intro' => 'Testing that unauthenticated updates do not overwrite the user ids.',
    ]);

    $originalCreatorId = $project->created_by_user_id;
    $originalUpdaterId = $project->updated_by_user_id;

    auth()->logout();

    $project->update(['title' => 'Updated Without Auth']);

    expect($project->fresh()->created_by_user_id)->toBe($originalCreatorId)
        ->and($project->fresh()->updated_by_user_id)->toBe($originalUpdaterId);
});

it('syncs image alt text to media name when a project is saved with rich content', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $uuid = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b';

    $project = Project::create([
        'title' => 'Project with Rich Content Image',
        'intro' => 'Intro text',
        'content' => '<p><img src="/storage/test.jpg" alt="Observer Test Alt" data-id="'.$uuid.'"></p>',
    ]);

    $media = App\Models\Media::create([
        'uuid' => $uuid,
        'model_type' => $project->getMorphClass(),
        'model_id' => $project->getKey(),
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

    // Trigger saved event by updating project
    $project->update([
        'title' => 'Updated Project Title',
    ]);

    expect($media->fresh()->name)->toBe('Observer Test Alt');
});

it('calls syncImageAltToMediaName in project observer saved method', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $uuid = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b';

    $project = Project::create([
        'title' => 'Project Observer Direct Test',
        'intro' => 'Intro text',
        'content' => '<p><img src="/storage/test.jpg" alt="Direct Observer Alt" data-id="'.$uuid.'"></p>',
    ]);

    $media = App\Models\Media::create([
        'uuid' => $uuid,
        'model_type' => $project->getMorphClass(),
        'model_id' => $project->getKey(),
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

    $observer = new App\Observers\ProjectObserver;
    $observer->saved($project);

    expect($media->fresh()->name)->toBe('Direct Observer Alt');
});
