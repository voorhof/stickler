<?php

/** @noinspection PhpUndefinedMethodInspection, PhpPossiblePolymorphicInvocationInspection */

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->author = User::factory()->create();
});

test('the Tag model uses the LogsActivity trait', function () {
    expect(in_array(LogsActivity::class, class_uses_recursive(Tag::class), true))->toBeTrue();
});

test('it logs an activity when a tag is created', function () {
    $tag = Tag::factory()->create([
        'created_by_user_id' => $this->author->id,
        'updated_by_user_id' => $this->author->id,
    ]);

    $activity = Activity::query()
        ->where('subject_type', $tag->getMorphClass())
        ->where('subject_id', $tag->id)
        ->where('event', 'created')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->event)->toBe('created')
        ->and($activity->subject->is($tag))->toBeTrue();
});

test('it logs an activity when a tag is updated', function () {
    $tag = Tag::factory()->create([
        'created_by_user_id' => $this->author->id,
        'updated_by_user_id' => $this->author->id,
    ]);

    $tag->update(['name' => 'A new name']);

    expect(Activity::query()
        ->where('subject_type', $tag->getMorphClass())
        ->where('subject_id', $tag->id)
        ->where('event', 'updated')
        ->exists())->toBeTrue();
});

test('it uses the authenticated user as the causer of tag activities', function () {
    auth()->login($this->author);

    $tag = Tag::factory()->create([
        'created_by_user_id' => $this->author->id,
        'updated_by_user_id' => $this->author->id,
    ]);

    $activity = Activity::query()
        ->where('subject_type', $tag->getMorphClass())
        ->where('subject_id', $tag->id)
        ->where('event', 'created')
        ->first();

    expect($activity->causer)->toBeInstanceOf(User::class)
        ->and($activity->causer->is($this->author))->toBeTrue();
});

test('it logs trashed event for soft-delete and deleted event for force delete', function () {
    $tag = Tag::factory()->create();

    // Soft delete
    $tag->delete();

    $activity = Activity::where('event', 'trashed')->latest()->first();
    expect($activity)->not->toBeNull()
        ->and($activity->subject->is($tag))->toBeTrue()
        ->and($activity->event)->toBe('trashed');

    // Force delete
    $deletedTagId = $tag->id;
    $tag->forceDelete();

    $activity = Activity::where('event', 'deleted')->latest()->first();
    expect($activity)->not->toBeNull()
        ->and($activity->subject_type)->toBe(Tag::class)
        ->and($activity->subject_id)->toBe($deletedTagId)
        ->and($activity->event)->toBe('deleted');
});
