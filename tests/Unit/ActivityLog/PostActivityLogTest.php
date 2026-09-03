<?php

/** @noinspection PhpUndefinedMethodInspection, PhpPossiblePolymorphicInvocationInspection */

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->author = User::factory()->create();
});

test('the Post model uses the LogsActivity trait', function () {
    expect(in_array(LogsActivity::class, class_uses_recursive(Post::class), true))->toBeTrue();
});

test('it logs an activity when a post is created', function () {
    $post = Post::factory()->create([
        'created_by_user_id' => $this->author->id,
        'updated_by_user_id' => $this->author->id,
    ]);

    $activity = Activity::query()
        ->where('subject_type', $post->getMorphClass())
        ->where('subject_id', $post->id)
        ->where('event', 'created')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->event)->toBe('created')
        ->and($activity->subject->is($post))->toBeTrue();
});

test('it logs an activity when a post is updated', function () {
    $post = Post::factory()->create([
        'created_by_user_id' => $this->author->id,
        'updated_by_user_id' => $this->author->id,
    ]);

    $post->update(['title' => 'A new title']);

    expect(Activity::query()
        ->where('subject_type', $post->getMorphClass())
        ->where('subject_id', $post->id)
        ->where('event', 'updated')
        ->exists())->toBeTrue();
});

test('it uses the authenticated user as the causer of post activities', function () {
    auth()->login($this->author);

    $post = Post::factory()->create([
        'created_by_user_id' => $this->author->id,
        'updated_by_user_id' => $this->author->id,
    ]);

    $activity = Activity::query()
        ->where('subject_type', $post->getMorphClass())
        ->where('subject_id', $post->id)
        ->where('event', 'created')
        ->first();

    expect($activity->causer)->toBeInstanceOf(User::class)
        ->and($activity->causer->is($this->author))->toBeTrue();
});

test('it logs trashed event for soft-delete and deleted event for force delete', function () {
    $post = Post::factory()->create();

    // Soft delete
    $post->delete();

    $activity = Activity::where('event', 'trashed')->latest()->first();
    expect($activity)->not->toBeNull()
        ->and($activity->subject->is($post))->toBeTrue()
        ->and($activity->event)->toBe('trashed');

    // Force delete
    $deletedPostId = $post->id;
    $post->forceDelete();

    $activity = Activity::where('event', 'deleted')->latest()->first();
    expect($activity)->not->toBeNull()
        ->and($activity->subject_type)->toBe(Post::class)
        ->and($activity->subject_id)->toBe($deletedPostId)
        ->and($activity->event)->toBe('deleted');
});
