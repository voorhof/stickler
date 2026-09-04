<?php

/** @noinspection PhpUndefinedMethodInspection, PhpPossiblePolymorphicInvocationInspection */

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->author = User::factory()->create();
});

test('the Message model uses the LogsActivity trait', function () {
    expect(in_array(LogsActivity::class, class_uses_recursive(Message::class), true))->toBeTrue();
});

test('it logs an activity when a message is created', function () {
    $message = Message::factory()->create([
        'created_by_user_id' => $this->author->id,
        'updated_by_user_id' => $this->author->id,
    ]);

    $activity = Activity::query()
        ->where('subject_type', $message->getMorphClass())
        ->where('subject_id', $message->id)
        ->where('event', 'created')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->event)->toBe('created')
        ->and($activity->subject->is($message))->toBeTrue();
});

test('it logs an activity when a message is updated', function () {
    $message = Message::factory()->create([
        'created_by_user_id' => $this->author->id,
        'updated_by_user_id' => $this->author->id,
    ]);

    $message->update(['subject' => 'A new title']);

    expect(Activity::query()
        ->where('subject_type', $message->getMorphClass())
        ->where('subject_id', $message->id)
        ->where('event', 'updated')
        ->exists())->toBeTrue();
});

test('it uses the authenticated user as the causer of message activities', function () {
    auth()->login($this->author);

    $message = Message::factory()->create([
        'created_by_user_id' => $this->author->id,
        'updated_by_user_id' => $this->author->id,
    ]);

    $activity = Activity::query()
        ->where('subject_type', $message->getMorphClass())
        ->where('subject_id', $message->id)
        ->where('event', 'created')
        ->first();

    expect($activity->causer)->toBeInstanceOf(User::class)
        ->and($activity->causer->is($this->author))->toBeTrue();
});

test('it logs trashed event for soft-delete and deleted event for force delete', function () {
    $message = Message::factory()->create();

    // Soft delete
    $message->delete();

    $activity = Activity::where('event', 'trashed')->latest()->first();
    expect($activity)->not->toBeNull()
        ->and($activity->subject->is($message))->toBeTrue()
        ->and($activity->event)->toBe('trashed');

    // Force delete
    $deletedMessageId = $message->id;
    $message->forceDelete();

    $activity = Activity::where('event', 'deleted')->latest()->first();
    expect($activity)->not->toBeNull()
        ->and($activity->subject_type)->toBe(Message::class)
        ->and($activity->subject_id)->toBe($deletedMessageId)
        ->and($activity->event)->toBe('deleted');
});
