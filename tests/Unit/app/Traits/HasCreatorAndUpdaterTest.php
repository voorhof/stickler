<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Message;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

test('it returns belongsTo relationship for creator', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    expect($post->creator())->toBeInstanceOf(BelongsTo::class);
});

test('it returns belongsTo relationship for updater', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    expect($post->updater())->toBeInstanceOf(BelongsTo::class);
});

test('creator relationship uses created_by_user_id foreign key', function () {
    $creator = User::factory()->create(['name' => 'Creator User']);
    User::factory()->create();
    $post = Post::factory()->create(['created_by_user_id' => $creator->id]);

    expect($post->creator->id)->toBe($creator->id);
});

test('updater relationship uses updated_by_user_id foreign key', function () {
    $updater = User::factory()->create(['name' => 'Updater User']);
    User::factory()->create();
    $post = Post::factory()->create(['updated_by_user_id' => $updater->id]);

    expect($post->updater->id)->toBe($updater->id);
});

test('creator returns default guest user when no creator exists', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $post->created_by_user_id = null;
    $post->saveQuietly();

    expect($post->creator->name)->toBe('Guest User');
});

test('updater returns default guest user when no updater exists', function () {
    User::factory()->create();
    $post = Post::factory()->create();

    $post->updated_by_user_id = null;
    $post->saveQuietly();

    expect($post->updater->name)->toBe('Guest User');
});

test('creator relationship includes soft deleted users', function () {
    $creator = User::factory()->create(['name' => 'Deleted Creator']);
    User::factory()->create();
    $post = Post::factory()->create(['created_by_user_id' => $creator->id]);

    $creator->delete();

    expect($post->creator->id)->toBe($creator->id)
        ->and($post->creator->name)->toBe('Deleted Creator');
});

test('updater relationship includes soft deleted users', function () {
    $updater = User::factory()->create(['name' => 'Deleted Updater']);
    User::factory()->create();
    $post = Post::factory()->create(['updated_by_user_id' => $updater->id]);

    $updater->delete();

    expect($post->updater->id)->toBe($updater->id)
        ->and($post->updater->name)->toBe('Deleted Updater');
});

test('user model uses self-referential creator relationship', function () {
    $creator = User::factory()->create();
    $createdUser = User::factory()->create(['created_by_user_id' => $creator->id]);

    expect($createdUser->creator->id)->toBe($creator->id);
});

test('user model creator does not include soft deleted users', function () {
    $creator = User::factory()->create(['name' => 'Deleted Creator']);
    $createdUser = User::factory()->create(['created_by_user_id' => $creator->id]);

    $creator->delete();

    expect($createdUser->creator->name)->toBe('Guest User');
});

test('it works across all model types', function () {
    $creator = User::factory()->create();
    $updater = User::factory()->create();
    User::factory()->create();

    $post = Post::factory()->create(['created_by_user_id' => $creator->id, 'updated_by_user_id' => $updater->id]);
    $page = Page::factory()->create(['created_by_user_id' => $creator->id, 'updated_by_user_id' => $updater->id]);
    $project = Project::factory()->create(['created_by_user_id' => $creator->id, 'updated_by_user_id' => $updater->id]);
    $message = Message::factory()->create(['created_by_user_id' => $creator->id, 'updated_by_user_id' => $updater->id]);
    $role = Role::factory()->create(['created_by_user_id' => $creator->id, 'updated_by_user_id' => $updater->id]);
    $tag = Tag::factory()->create(['created_by_user_id' => $creator->id, 'updated_by_user_id' => $updater->id]);

    expect($post->creator->id)->toBe($creator->id)
        ->and($page->creator->id)->toBe($creator->id)
        ->and($project->creator->id)->toBe($creator->id)
        ->and($message->creator->id)->toBe($creator->id)
        ->and($role->creator->id)->toBe($creator->id)
        ->and($tag->creator->id)->toBe($creator->id)
        ->and($post->updater->id)->toBe($updater->id)
        ->and($page->updater->id)->toBe($updater->id)
        ->and($project->updater->id)->toBe($updater->id)
        ->and($message->updater->id)->toBe($updater->id)
        ->and($role->updater->id)->toBe($updater->id)
        ->and($tag->updater->id)->toBe($updater->id);
});
