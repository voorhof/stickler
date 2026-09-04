<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\HasActivity;

uses(RefreshDatabase::class);

test('the User model uses the HasActivity trait', function () {
    expect(in_array(HasActivity::class, class_uses_recursive(User::class), true))->toBeTrue();
});

test('it logs an activity when a user is created', function () {
    $user = User::factory()->create();

    $activity = Activity::query()
        ->where('subject_type', $user->getMorphClass())
        ->where('subject_id', $user->id)
        ->where('event', 'created')
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->log_name)->toBe('default')
        ->and($activity->event)->toBe('created')
        ->and($activity->subject)->toBeInstanceOf(User::class)
        ->and($activity->subject->is($user))->toBeTrue();
});

test('it logs an activity when a user is updated', function () {
    $user = User::factory()->create();

    $user->update(['name' => 'Updated']);

    $activity = Activity::query()
        ->where('subject_type', $user->getMorphClass())
        ->where('subject_id', $user->id)
        ->where('event', 'updated')
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->event)->toBe('updated');
});

test('it automatically resolves the authenticated user as the causer', function () {
    $causer = User::factory()->create();
    auth()->login($causer);

    $subject = User::factory()->create();

    $activity = Activity::query()
        ->where('subject_type', $subject->getMorphClass())
        ->where('subject_id', $subject->id)
        ->where('event', 'created')
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer)->toBeInstanceOf(User::class)
        ->and($activity->causer->is($causer))->toBeTrue();
});

test('it exposes the activitiesAsSubject relationship', function () {
    $user = User::factory()->create();
    $user->update(['name' => 'Another']);

    expect($user->activitiesAsSubject()->count())->toBeGreaterThanOrEqual(2);
});

test('it exposes the activities relationship as an alias of activitiesAsSubject', function () {
    $user = User::factory()->create();

    expect($user->activities()->count())
        ->toBe($user->activitiesAsSubject()->count());
});

test('it exposes the activitiesAsCauser relationship from the HasActivity trait', function () {
    $causer = User::factory()->create();
    auth()->login($causer);

    // Cause an activity by creating another user.
    User::factory()->create();

    expect($causer->activitiesAsCauser()->count())->toBeGreaterThanOrEqual(1);
});

test('it logs trashed event for soft-delete and deleted event for force delete', function () {
    $user = User::factory()->create();

    // Soft delete
    $user->delete();

    $activity = Activity::where('event', 'trashed')->latest()->first();
    expect($activity)->not->toBeNull()
        ->and($activity->subject->is($user))->toBeTrue()
        ->and($activity->event)->toBe('trashed');

    // Force delete
    $deletedUserId = $user->id;
    $user->forceDelete();

    $activity = Activity::where('event', 'deleted')->latest()->first();
    expect($activity)->not->toBeNull()
        ->and($activity->subject_type)->toBe(User::class)
        ->and($activity->subject_id)->toBe($deletedUserId)
        ->and($activity->event)->toBe('deleted');
});
