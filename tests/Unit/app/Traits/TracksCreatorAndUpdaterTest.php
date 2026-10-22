<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

describe('relationship types', function () {
    test('it has relations for created and updated media', function () {
        $user = User::factory()->create();

        expect($user->createdMedia())->toBeInstanceOf(HasMany::class)
            ->and($user->updatedMedia())->toBeInstanceOf(HasMany::class);
    });

    test('it has relations for created and updated messages', function () {
        $user = User::factory()->create();

        expect($user->createdMessages())->toBeInstanceOf(HasMany::class)
            ->and($user->updatedMessages())->toBeInstanceOf(HasMany::class);
    });

    test('it has relations for created and updated posts', function () {
        $user = User::factory()->create();

        expect($user->createdPosts())->toBeInstanceOf(HasMany::class)
            ->and($user->updatedPosts())->toBeInstanceOf(HasMany::class);
    });

    test('it has relations for created and updated pages', function () {
        $user = User::factory()->create();

        expect($user->createdPages())->toBeInstanceOf(HasMany::class)
            ->and($user->updatedPages())->toBeInstanceOf(HasMany::class);
    });

    test('it has relations for created and updated projects', function () {
        $user = User::factory()->create();

        expect($user->createdProjects())->toBeInstanceOf(HasMany::class)
            ->and($user->updatedProjects())->toBeInstanceOf(HasMany::class);
    });

    test('it has relations for created and updated roles', function () {
        $user = User::factory()->create();

        expect($user->createdRoles())->toBeInstanceOf(HasMany::class)
            ->and($user->updatedRoles())->toBeInstanceOf(HasMany::class);
    });

    test('it has relations for created and updated tags', function () {
        $user = User::factory()->create();

        expect($user->createdTags())->toBeInstanceOf(HasMany::class)
            ->and($user->updatedTags())->toBeInstanceOf(HasMany::class);
    });

    test('it has relations for created and updated users', function () {
        $user = User::factory()->create();

        expect($user->createdUsers())->toBeInstanceOf(HasMany::class)
            ->and($user->updatedUsers())->toBeInstanceOf(HasMany::class);
    });
});

describe('foreign keys', function () {
    test('created relationships use the correct foreign key', function () {
        $user = User::factory()->create();

        Post::factory()->create([
            'created_by_user_id' => $user->id,
            'updated_by_user_id' => null,
        ]);

        expect($user->createdPosts()->count())->toBe(1)
            ->and($user->updatedPosts()->count())->toBe(0);
    });

    test('updated relationships use the correct foreign key', function () {
        $otherUser = User::factory()->create();
        $user = User::factory()->create();

        Post::factory()->create([
            'created_by_user_id' => $otherUser->id,
            'updated_by_user_id' => $user->id,
        ]);

        expect($user->createdPosts()->count())->toBe(0)
            ->and($user->updatedPosts()->count())->toBe(1);
    });
});

describe('model retrieval', function () {
    test('created relationships return related models', function () {
        $user = User::factory()->create();

        Post::factory()->count(3)->create([
            'created_by_user_id' => $user->id,
        ]);

        $createdPosts = $user->createdPosts()->get();

        expect($createdPosts)->toHaveCount(3)
            ->and($createdPosts->first())->toBeInstanceOf(Post::class);
    });

    test('updated relationships return related models', function () {
        $user = User::factory()->create();

        Post::factory()->count(2)->create([
            'updated_by_user_id' => $user->id,
        ]);

        $updatedPosts = $user->updatedPosts()->get();

        expect($updatedPosts)->toHaveCount(2)
            ->and($updatedPosts->first())->toBeInstanceOf(Post::class);
    });
});

describe('self relationships', function () {
    test('it can query created users through self relationship', function () {
        $creator = User::factory()->create();

        User::factory()->count(2)->create([
            'created_by_user_id' => $creator->id,
            'updated_by_user_id' => $creator->id,
        ]);

        expect($creator->createdUsers()->where('id', '!=', $creator->id)->count())->toBe(2);
    });

    test('it can query updated users through self relationship', function () {
        $updater = User::factory()->create();

        User::factory()->count(3)->create([
            'created_by_user_id' => $updater->id,
            'updated_by_user_id' => $updater->id,
        ]);

        expect($updater->updatedUsers()->where('id', '!=', $updater->id)->count())->toBe(3);
    });
});
