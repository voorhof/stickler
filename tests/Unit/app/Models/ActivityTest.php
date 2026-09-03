<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

use App\Models\Activity;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Enums\ActivityEvent;
use Spatie\Activitylog\Support\LogOptions;

uses(RefreshDatabase::class);

test('an activity can be created', function () {
    $activity = Activity::create([
        'log_name' => 'custom_log',
        'description' => 'Something happened',
        'event' => 'created',
    ]);

    expect($activity->log_name)->toBe('custom_log')
        ->and($activity->description)->toBe('Something happened')
        ->and($activity->event)->toBe('created');
});

test('it casts attribute_changes and properties to collections', function () {
    $activity = Activity::create([
        'description' => 'Test casts',
        'attribute_changes' => ['old' => ['name' => 'Old'], 'attributes' => ['name' => 'New']],
        'properties' => ['key' => 'value'],
    ]);

    expect($activity->attribute_changes)->toBeInstanceOf(Collection::class)
        ->and($activity->attribute_changes->get('attributes'))->toBe(['name' => 'New'])
        ->and($activity->properties)->toBeInstanceOf(Collection::class)
        ->and($activity->properties->get('key'))->toBe('value');
});

test('it has a subject relationship', function () {
    User::factory()->create();

    $post = Post::factory()->create();

    $activity = Activity::create([
        'description' => 'Post activity',
        'subject_id' => $post->id,
        'subject_type' => $post->getMorphClass(),
    ]);

    expect($activity->subject)->toBeInstanceOf(Post::class)
        ->and($activity->subject->id)->toBe($post->id);
});

test('it has a causer relationship', function () {
    $user = User::factory()->create();

    $activity = Activity::create([
        'description' => 'User did something',
        'causer_id' => $user->id,
        'causer_type' => $user->getMorphClass(),
    ]);

    expect($activity->causer)->toBeInstanceOf(User::class)
        ->and($activity->causer->id)->toBe($user->id);
});

test('it can retrieve properties using getProperty helper', function () {
    $activity = Activity::create([
        'description' => 'Test property',
        'properties' => ['nested' => ['key' => 'value'], 'simple' => 'simple_value'],
    ]);

    expect($activity->getProperty('simple'))->toBe('simple_value')
        ->and($activity->getProperty('nested.key'))->toBe('value')
        ->and($activity->getProperty('non_existent', 'default'))->toBe('default');
});

test('it has an inLog scope', function () {
    Activity::truncate();
    Activity::create(['description' => 'Log A', 'log_name' => 'log_a']);
    Activity::create(['description' => 'Log B', 'log_name' => 'log_b']);
    Activity::create(['description' => 'Log C', 'log_name' => 'log_c']);

    expect(Activity::inLog('log_a')->count())->toBe(1)
        ->and(Activity::inLog(['log_a', 'log_b'])->count())->toBe(2)
        ->and(Activity::inLog(['log_b', 'log_c'])->count())->toBe(2);
});

test('it has a causedBy scope', function () {
    Activity::truncate();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Activity::create(['description' => 'By User 1', 'causer_id' => $user1->id, 'causer_type' => $user1->getMorphClass()]);
    Activity::create(['description' => 'By User 2', 'causer_id' => $user2->id, 'causer_type' => $user2->getMorphClass()]);

    expect(Activity::causedBy($user1)->count())->toBe(1)
        ->and(Activity::causedBy($user1)->first()->description)->toBe('By User 1');
});

test('it has a forSubject scope', function () {
    Activity::truncate();
    User::factory()->create();
    $post1 = Post::factory()->create();
    $post2 = Post::factory()->create();

    // The factory above might have created logs already.
    Activity::truncate();

    Activity::create(['description' => 'For Post 1', 'subject_id' => $post1->id, 'subject_type' => $post1->getMorphClass()]);
    Activity::create(['description' => 'For Post 2', 'subject_id' => $post2->id, 'subject_type' => $post2->getMorphClass()]);

    $activities = Activity::forSubject($post1)->get();

    expect($activities->count())->toBe(1)
        ->and($activities->first()->description)->toBe('For Post 1');
});

test('it has a forEvent scope', function () {
    Activity::truncate();
    Activity::create(['description' => 'Created', 'event' => 'created']);
    Activity::create(['description' => 'Updated', 'event' => 'updated']);
    Activity::create(['description' => 'Deleted', 'event' => 'deleted']);

    expect(Activity::forEvent('created')->count())->toBe(1)
        ->and(Activity::forEvent(ActivityEvent::Deleted)->count())->toBe(1)
        ->and(Activity::forEvent('updated')->first()->description)->toBe('Updated');
});

test('it returns description as title', function () {
    $activity = Activity::create([
        'description' => 'Plain activity description',
    ]);

    expect($activity->title)->toBe('Plain activity description');
});

test('it formats activityModelRoute for various model types', function () {
    $activity = new Activity;

    expect($activity->activityModelRoute('App\Models\Post'))->toBe('filament.admin.resources.posts.edit')
        ->and($activity->activityModelRoute('App\Models\Media'))->toBe('filament.admin.resources.media.edit')
        ->and($activity->activityModelRoute('App\Models\User'))->toBe('filament.admin.resources.users.edit')
        ->and($activity->activityModelRoute('App\Models\Role'))->toBe('filament.admin.resources.roles.edit');
});

test('it configures activity log options correctly', function () {
    $activity = new Activity;
    $options = $activity->getActivitylogOptions();

    expect($options)->toBeInstanceOf(LogOptions::class);
});
