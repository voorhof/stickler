<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

it('logs a basic activity via the activity() helper', function () {
    activity()->log('Look mum, I logged something');

    $activity = Activity::latest('id')->first();

    expect($activity)->not->toBeNull()
        ->and($activity->log_name)->toBe('default')
        ->and($activity->description)->toBe('Look mum, I logged something')
        ->and($activity->subject)->toBeNull()
        ->and($activity->causer)->toBeNull();
});

it('logs an activity with an explicit causer and subject', function () {
    $causer = User::factory()->create();
    $subject = User::factory()->create();

    activity()
        ->causedBy($causer)
        ->performedOn($subject)
        ->log('created');

    $activity = Activity::query()
        ->where('description', 'created')
        ->where('subject_id', $subject->id)
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->subject->is($subject))->toBeTrue()
        ->and($activity->causer->is($causer))->toBeTrue();
});

it('supports the short by() and on() aliases', function () {
    $causer = User::factory()->create();
    $subject = User::factory()->create();

    activity()
        ->by($causer)
        ->on($subject)
        ->log('updated');

    $activity = Activity::query()
        ->where('description', 'updated')
        ->where('subject_id', $subject->id)
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->subject->is($subject))->toBeTrue()
        ->and($activity->causer->is($causer))->toBeTrue();
});

it('automatically uses the authenticated user as the causer', function () {
    $user = User::factory()->create();
    auth()->login($user);

    activity()->log('logged in');

    $activity = Activity::query()
        ->where('description', 'logged in')
        ->latest('id')
        ->first();

    expect($activity->causer)->toBeInstanceOf(User::class)
        ->and($activity->causer->is($user))->toBeTrue();
});

it('can set a custom event name', function () {
    $user = User::factory()->create();
    $subject = User::factory()->create();

    activity()
        ->causedBy($user)
        ->performedOn($subject)
        ->event('promoted')
        ->log('Promoted user to admin');

    $activity = Activity::query()
        ->where('description', 'Promoted user to admin')
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->event)->toBe('promoted');
});

it('can store custom properties on the activity', function () {
    activity()
        ->withProperties(['key' => 'value', 'count' => 3])
        ->log('with properties');

    $activity = Activity::query()
        ->where('description', 'with properties')
        ->latest('id')
        ->first();

    expect($activity->properties->get('key'))->toBe('value')
        ->and($activity->properties->get('count'))->toBe(3);
});

it('does not log activity when the package is disabled via config', function () {
    config()->set('activitylog.enabled', false);

    activity()->log('this should not be saved');
    User::factory()->create();

    expect(Activity::count())->toBe(0);
});

it('uses the default log name from the configuration', function () {
    activity()->log('a default-log-named entry');

    $activity = Activity::query()
        ->where('description', 'a default-log-named entry')
        ->latest('id')
        ->first();

    expect($activity->log_name)->toBe(config('activitylog.default_log_name'));
});

it('stores the subject through a polymorphic relation for various models', function () {
    $causer = User::factory()->create();
    $post = Post::factory()->create([
        'created_by_user_id' => $causer->id,
        'updated_by_user_id' => $causer->id,
    ]);

    activity()
        ->causedBy($causer)
        ->performedOn($post)
        ->event('reviewed')
        ->log('Reviewed the post');

    $activity = Activity::query()
        ->where('description', 'Reviewed the post')
        ->latest('id')
        ->first();

    expect($activity->subject)->toBeInstanceOf(Post::class)
        ->and($activity->subject->is($post))->toBeTrue()
        ->and($activity->event)->toBe('reviewed');
});
