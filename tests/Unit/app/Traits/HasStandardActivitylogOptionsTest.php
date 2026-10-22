<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Activity;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Spatie\Activitylog\Support\LogOptions;

test('it returns log options instance', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($post->getActivitylogOptions())->toBeInstanceOf(LogOptions::class);
});

test('it logs only fillable attributes by default', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    $options = $post->getActivitylogOptions();

    expect($options->logFillable)->toBeTrue();
});

test('it logs only dirty attributes', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    $options = $post->getActivitylogOptions();

    expect($options->logOnlyDirty)->toBeTrue();
});

test('it does not log empty changes', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    $options = $post->getActivitylogOptions();

    expect($options->logEmptyChanges)->toBeFalse();
});

test('it ignores updated_at changes by default', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    $options = $post->getActivitylogOptions();

    expect($options->dontLogIfAttributesChangedOnly)->toContain('updated_at');
});

test('it sets description for event', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    $options = $post->getActivitylogOptions();

    expect($options->descriptionForEvent)->not()->toBeNull();
});

test('user model excludes password from logging', function () {
    $user = User::factory()->create();

    $options = $user->getActivitylogOptions();

    expect($options->logExceptAttributes)->toContain('password');
});

test('user model ignores updated_by_user_id changes', function () {
    $user = User::factory()->create();

    $options = $user->getActivitylogOptions();

    expect($options->dontLogIfAttributesChangedOnly)->toContain('updated_by_user_id');
});

test('media model logs all attributes', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);
    $media = Media::create([
        'model_id' => $post->id,
        'model_type' => Post::class,
        'collection_name' => 'images',
        'name' => 'test.jpg',
        'file_name' => 'test.jpg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => '[]',
        'custom_properties' => '{}',
        'generated_conversions' => '[]',
        'responsive_images' => '[]',
    ]);

    $options = $media->getActivitylogOptions();

    expect($options->logFillable)->toBeFalse();
});

test('media model excludes generated conversions from logging', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);
    $media = Media::create([
        'model_id' => $post->id,
        'model_type' => Post::class,
        'collection_name' => 'images',
        'name' => 'test.jpg',
        'file_name' => 'test.jpg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => '[]',
        'custom_properties' => '{}',
        'generated_conversions' => '[]',
        'responsive_images' => '[]',
    ]);

    $options = $media->getActivitylogOptions();

    expect($options->logExceptAttributes)->toContain('generated_conversions')
        ->and($options->logExceptAttributes)->toContain('responsive_images');
});

test('activity model uses custom log name', function () {
    $activity = Activity::create([
        'log_name' => 'default',
        'description' => 'Test activity',
    ]);

    $options = $activity->getActivitylogOptions();

    expect($options->logName)->toBe('activity');
});

test('activity model logs all attributes', function () {
    $activity = Activity::create([
        'log_name' => 'default',
        'description' => 'Test activity',
    ]);

    $options = $activity->getActivitylogOptions();

    expect($options->logFillable)->toBeFalse();
});

test('activity model ignores updated_by changes', function () {
    $activity = Activity::create([
        'log_name' => 'default',
        'description' => 'Test activity',
    ]);

    $options = $activity->getActivitylogOptions();

    expect($options->dontLogIfAttributesChangedOnly)->toContain('updated_by');
});

test('it works across all standard model types', function () {
    $user = User::factory()->create();

    $post = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);
    $page = App\Models\Page::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);
    $project = App\Models\Project::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);
    $message = App\Models\Message::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);
    $role = App\Models\Role::factory()->create();
    $tag = App\Models\Tag::factory()->create();
    $permission = App\Models\Permission::factory()->create();

    expect($post->getActivitylogOptions())->toBeInstanceOf(LogOptions::class)
        ->and($page->getActivitylogOptions())->toBeInstanceOf(LogOptions::class)
        ->and($project->getActivitylogOptions())->toBeInstanceOf(LogOptions::class)
        ->and($message->getActivitylogOptions())->toBeInstanceOf(LogOptions::class)
        ->and($role->getActivitylogOptions())->toBeInstanceOf(LogOptions::class)
        ->and($tag->getActivitylogOptions())->toBeInstanceOf(LogOptions::class)
        ->and($permission->getActivitylogOptions())->toBeInstanceOf(LogOptions::class);
});
