<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Imports\PostImporter;
use App\Models\Post;
use App\Models\User;
use Filament\Actions\Imports\Models\Import;

describe('importing posts', function () {
    test('post importer validates date rule and imports valid row', function () {
        User::factory()->create();

        PostImporter::test()
            ->import([
                'slug' => 'test-post',
                'title' => 'Test Post',
                'intro' => 'This is a test post intro.',
                'published_at' => '2026-10-21 12:00:00',
            ])
            ->assertImported();

        expect(Post::where('slug', 'test-post')->exists())->toBeTrue();
    });

    test('post importer fails validation with invalid date', function () {
        PostImporter::test()
            ->import([
                'slug' => 'test-post',
                'title' => 'Test Post',
                'intro' => 'This is a test post intro.',
                'published_at' => 'not-a-date',
            ])
            ->assertHasErrors(['published_at' => 'date']);
    });

    test('post importer allows null or empty published at', function () {
        PostImporter::test()
            ->import([
                'slug' => 'test-post-nullable',
                'title' => 'Test Post Nullable',
                'intro' => 'This is a test post intro.',
                'published_at' => null,
            ])
            ->assertImported();

        expect(Post::where('slug', 'test-post-nullable')->first()->published_at)->toBeNull();
    });
});

describe('user tracking', function () {
    test('post importer sets created by and updated by user id to the user executing the import', function () {
        $user = User::factory()->create();

        $import = new Import([
            'user_id' => $user->id,
        ]);

        PostImporter::test(import: $import)
            ->import([
                'slug' => 'test-post-user-id',
                'title' => 'Test Post User ID',
                'intro' => 'This is a test post intro.',
            ])
            ->assertImported();

        $post = Post::where('slug', 'test-post-user-id')->first();
        expect($post->created_by_user_id)->toBe($user->id)
            ->and($post->updated_by_user_id)->toBe($user->id);
    });

    test('post importer sets created by and updated by user id from authenticated user', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        PostImporter::test()
            ->import([
                'slug' => 'test-post-auth-user',
                'title' => 'Test Post Auth User',
                'intro' => 'This is a test post intro.',
            ])
            ->assertImported();

        $post = Post::where('slug', 'test-post-auth-user')->first();
        expect($post->created_by_user_id)->toBe($user->id)
            ->and($post->updated_by_user_id)->toBe($user->id);
    });
});
