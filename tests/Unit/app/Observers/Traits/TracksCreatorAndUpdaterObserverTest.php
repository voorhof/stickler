<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Message;
use App\Models\Post;
use App\Models\User;
use App\Observers\MediaObserver;
use App\Observers\MessageObserver;
use App\Observers\PostObserver;
use App\Observers\RoleObserver;
use App\Observers\TagObserver;
use App\Observers\UserObserver;

describe('TracksCreatorAndUpdaterObserver trait methods', function () {

    test('trait provides creating method on all observers', function () {
        expect(method_exists(new MessageObserver, 'creating'))->toBeTrue()
            ->and(method_exists(new PostObserver, 'creating'))->toBeTrue()
            ->and(method_exists(new RoleObserver, 'creating'))->toBeTrue()
            ->and(method_exists(new TagObserver, 'creating'))->toBeTrue()
            ->and(method_exists(new UserObserver, 'creating'))->toBeTrue()
            ->and(method_exists(new MediaObserver, 'creating'))->toBeTrue();
    });

    test('trait provides updating method on all observers', function () {
        expect(method_exists(new MessageObserver, 'updating'))->toBeTrue()
            ->and(method_exists(new PostObserver, 'updating'))->toBeTrue()
            ->and(method_exists(new RoleObserver, 'updating'))->toBeTrue()
            ->and(method_exists(new TagObserver, 'updating'))->toBeTrue()
            ->and(method_exists(new UserObserver, 'updating'))->toBeTrue()
            ->and(method_exists(new MediaObserver, 'updating'))->toBeTrue();
    });
});

describe('creating event', function () {

    test('sets creator and updater when authenticated', function () {
        $user = User::factory()->create();

        auth()->login($user);

        $post = Post::factory()->make(['title' => 'Test Post']);

        $observer = new PostObserver;
        $observer->creating($post);

        expect($post->created_by_user_id)->toBe($user->id)
            ->and($post->updated_by_user_id)->toBe($user->id);
    });

    test('does not set ids when not authenticated', function () {
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
});

describe('updating event', function () {

    test('sets updater when authenticated', function () {
        $user = User::factory()->create();

        auth()->login($user);

        $message = Message::factory()->make(['subject' => 'Test Message']);

        $observer = new MessageObserver;
        $observer->updating($message);

        expect($message->updated_by_user_id)->toBe($user->id);
    });

    test('does not set id when not authenticated', function () {
        auth()->logout();

        $message = Message::factory()->make(['subject' => 'Test Message']);

        $observer = new MessageObserver;
        $observer->updating($message);

        expect($message->updated_by_user_id)->toBeNull();
    });
});
