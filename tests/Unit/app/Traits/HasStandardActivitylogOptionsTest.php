<?php /** @noinspection PhpUndefinedFieldInspection */

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Activity;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Spatie\Activitylog\Support\LogOptions;

test('it returns log options instance', function () {
    $post = Post::factory()->create();

    expect($post->getActivitylogOptions())->toBeInstanceOf(LogOptions::class);
});

test('it logs only fillable attributes by default', function () {
    $post = Post::factory()->create();

    $options = $post->getActivitylogOptions();

    expect($options->logOnlyFillable)->toBeTrue();
});

test('it logs only dirty attributes', function () {
    $post = Post::factory()->create();

    $options = $post->getActivitylogOptions();

    expect($options->logOnlyDirty)->toBeTrue();
});

test('it does not log empty changes', function () {
    $post = Post::factory()->create();

    $options = $post->getActivitylogOptions();

    expect($options->doNotLogWhenNoChanges)->toBeTrue();
});

test('it ignores updated_at changes by default', function () {
    $post = Post::factory()->create();

    $options = $post->getActivitylogOptions();

    expect($options->dontLogIfAttributesChangedOnly)->toContain('updated_at');
});

test('it sets description for event', function () {
    $post = Post::factory()->create();

    $options = $post->getActivitylogOptions();

    expect($options->descriptionForEvent)->not()->toBeNull();
});

test('user model excludes password from logging', function () {
    $user = User::factory()->create();

    $options = $user->getActivitylogOptions();

    expect($options->logExcept)->toContain('password');
});

test('user model ignores updated_by_user_id changes', function () {
    $user = User::factory()->create();

    $options = $user->getActivitylogOptions();

    expect($options->dontLogIfAttributesChangedOnly)->toContain('updated_by_user_id');
});

test('media model logs all attributes', function () {
    User::factory()->create();
    $post = Post::factory()->create();
    $media = Media::factory()->create([
        'model_id' => $post->id,
        'model_type' => Post::class,
        'collection_name' => 'images',
        'name' => 'test.jpg',
        'file_name' => 'test.jpg',
    ]);

    $options = $media->getActivitylogOptions();

    expect($options->logOnlyFillable)->toBeFalse();
});

test('media model excludes generated conversions from logging', function () {
    User::factory()->create();
    $post = Post::factory()->create();
    $media = Media::factory()->create([
        'model_id' => $post->id,
        'model_type' => Post::class,
        'collection_name' => 'images',
        'name' => 'test.jpg',
        'file_name' => 'test.jpg',
    ]);

    $options = $media->getActivitylogOptions();

    expect($options->logExcept)->toContain('generated_conversions')
        ->and($options->logExcept)->toContain('responsive_images');
});

test('activity model uses custom log name', function () {
    $activity = Activity::factory()->create();

    $options = $activity->getActivitylogOptions();

    expect($options->logName)->toBe('activity');
});

test('activity model logs all attributes', function () {
    $activity = Activity::factory()->create();

    $options = $activity->getActivitylogOptions();

    expect($options->logOnlyFillable)->toBeFalse();
});

test('activity model ignores updated_by changes', function () {
    $activity = Activity::factory()->create();

    $options = $activity->getActivitylogOptions();

    expect($options->dontLogIfAttributesChangedOnly)->toContain('updated_by');
});

test('it works across all standard model types', function () {
    User::factory()->create();

    $post = Post::factory()->create();
    $page = App\Models\Page::factory()->create();
    $project = App\Models\Project::factory()->create();
    $message = App\Models\Message::factory()->create();
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
