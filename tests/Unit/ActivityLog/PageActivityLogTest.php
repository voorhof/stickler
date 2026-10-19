<?php

/** @noinspection PhpUndefinedMethodInspection, PhpPossiblePolymorphicInvocationInspection */

use App\Models\Page;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

beforeEach(function () {
    $this->author = User::factory()->create();
});

test('the Page model uses the LogsActivity trait', function () {
    expect(in_array(LogsActivity::class, class_uses_recursive(Page::class), true))->toBeTrue();
});

test('it logs an activity when a page is created', function () {
    $page = Page::factory()->create([
        'created_by_user_id' => $this->author->id,
        'updated_by_user_id' => $this->author->id,
    ]);

    $activity = Activity::query()
        ->where('subject_type', $page->getMorphClass())
        ->where('subject_id', $page->id)
        ->where('event', 'created')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->event)->toBe('created')
        ->and($activity->subject->is($page))->toBeTrue();
});

test('it logs an activity when a page is updated', function () {
    $page = Page::factory()->create([
        'created_by_user_id' => $this->author->id,
        'updated_by_user_id' => $this->author->id,
    ]);

    $page->update(['title' => 'A new title']);

    expect(Activity::query()
        ->where('subject_type', $page->getMorphClass())
        ->where('subject_id', $page->id)
        ->where('event', 'updated')
        ->exists())->toBeTrue();
});

test('it uses the authenticated user as the causer of page activities', function () {
    auth()->login($this->author);

    $page = Page::factory()->create([
        'created_by_user_id' => $this->author->id,
        'updated_by_user_id' => $this->author->id,
    ]);

    $activity = Activity::query()
        ->where('subject_type', $page->getMorphClass())
        ->where('subject_id', $page->id)
        ->where('event', 'created')
        ->first();

    expect($activity->causer)->toBeInstanceOf(User::class)
        ->and($activity->causer->is($this->author))->toBeTrue();
});

test('it logs trashed event for soft-delete and deleted event for force delete', function () {
    $page = Page::factory()->create();

    // Soft delete
    $page->delete();

    $activity = Activity::where('event', 'trashed')->latest()->first();
    expect($activity)->not->toBeNull()
        ->and($activity->subject->is($page))->toBeTrue()
        ->and($activity->event)->toBe('trashed');

    // Force delete
    $deletedPageId = $page->id;
    $page->forceDelete();

    $activity = Activity::where('event', 'deleted')->latest()->first();
    expect($activity)->not->toBeNull()
        ->and($activity->subject_type)->toBe(Page::class)
        ->and($activity->subject_id)->toBe($deletedPageId)
        ->and($activity->event)->toBe('deleted');
});
