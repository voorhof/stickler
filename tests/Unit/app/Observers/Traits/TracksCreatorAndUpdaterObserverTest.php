<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Message;
use App\Models\Post;
use App\Models\User;
use App\Observers\MessageObserver;
use App\Observers\PostObserver;

test('TracksCreatorAndUpdaterObserver trait provides creating method', function () {
    $observer = new MessageObserver;

    expect(method_exists($observer, 'creating'))->toBeTrue();
});

test('TracksCreatorAndUpdaterObserver trait provides updating method', function () {
    $observer = new MessageObserver;

    expect(method_exists($observer, 'updating'))->toBeTrue();
});

test('creating sets creator and updater when authenticated', function () {
    $user = User::factory()->create();

    auth()->login($user);

    $post = Post::factory()->make([
        'title' => 'Test Post',
    ]);

    $observer = new PostObserver;
    $observer->creating($post);

    expect($post->created_by_user_id)->toBe($user->id)
        ->and($post->updated_by_user_id)->toBe($user->id);
});

test('creating does not set ids when not authenticated', function () {
    auth()->logout();

    $post = Post::factory()->make([
        'title' => 'Test Post',
        'created_by_user_id' => null,
        'updated_by_user_id' => null,
    ]);

    $observer = new PostObserver;
    $observer->creating($post);

    expect($post->created_by_user_id)->toBeNull()
        ->and($post->updated_by_user_id)->toBeNull();
});

test('updating sets updater when authenticated', function () {
    $user = User::factory()->create();

    auth()->login($user);

    $message = Message::factory()->make([
        'subject' => 'Test Message',
    ]);

    $observer = new MessageObserver;
    $observer->updating($message);

    expect($message->updated_by_user_id)->toBe($user->id);
});

test('updating does not set id when not authenticated', function () {
    auth()->logout();

    $message = Message::factory()->make([
        'subject' => 'Test Message',
    ]);

    $observer = new MessageObserver;
    $observer->updating($message);

    expect($message->updated_by_user_id)->toBeNull();
});
