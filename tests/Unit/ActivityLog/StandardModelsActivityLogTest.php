<?php

use App\Models\Message;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

beforeEach(function () {
    $this->author = User::factory()->create();
});

describe('trait registration', function () {
    test('the Post model uses the LogsActivity trait', function () {
        expect(in_array(LogsActivity::class, class_uses_recursive(Post::class), true))->toBeTrue();
    });

    test('the Page model uses the LogsActivity trait', function () {
        expect(in_array(LogsActivity::class, class_uses_recursive(Page::class), true))->toBeTrue();
    });

    test('the Project model uses the LogsActivity trait', function () {
        expect(in_array(LogsActivity::class, class_uses_recursive(Project::class), true))->toBeTrue();
    });

    test('the Message model uses the LogsActivity trait', function () {
        expect(in_array(LogsActivity::class, class_uses_recursive(Message::class), true))->toBeTrue();
    });

    test('the Tag model uses the LogsActivity trait', function () {
        expect(in_array(LogsActivity::class, class_uses_recursive(Tag::class), true))->toBeTrue();
    });
});

describe('create activity logging', function () {
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

    test('it logs an activity when a project is created', function () {
        $project = Project::factory()->create([
            'created_by_user_id' => $this->author->id,
            'updated_by_user_id' => $this->author->id,
        ]);

        $activity = Activity::query()
            ->where('subject_type', $project->getMorphClass())
            ->where('subject_id', $project->id)
            ->where('event', 'created')
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->event)->toBe('created')
            ->and($activity->subject->is($project))->toBeTrue();
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
});

describe('update activity logging', function () {
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

    test('it logs an activity when a project is updated', function () {
        $project = Project::factory()->create([
            'created_by_user_id' => $this->author->id,
            'updated_by_user_id' => $this->author->id,
        ]);
        $project->update(['title' => 'A new title']);

        expect(Activity::query()
            ->where('subject_type', $project->getMorphClass())
            ->where('subject_id', $project->id)
            ->where('event', 'updated')
            ->exists())->toBeTrue();
    });

    test('it logs an activity when a message is updated', function () {
        $message = Message::factory()->create([
            'created_by_user_id' => $this->author->id,
            'updated_by_user_id' => $this->author->id,
        ]);
        $message->update(['subject' => 'A new subject']);

        expect(Activity::query()
            ->where('subject_type', $message->getMorphClass())
            ->where('subject_id', $message->id)
            ->where('event', 'updated')
            ->exists())->toBeTrue();
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
});

describe('causer tracking', function () {
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

    test('it uses the authenticated user as the causer of project activities', function () {
        auth()->login($this->author);

        $project = Project::factory()->create([
            'created_by_user_id' => $this->author->id,
            'updated_by_user_id' => $this->author->id,
        ]);

        $activity = Activity::query()
            ->where('subject_type', $project->getMorphClass())
            ->where('subject_id', $project->id)
            ->where('event', 'created')
            ->first();

        expect($activity->causer)->toBeInstanceOf(User::class)
            ->and($activity->causer->is($this->author))->toBeTrue();
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
});

describe('delete activity logging', function () {
    test('it logs trashed and deleted events for post', function () {
        $post = Post::factory()->create();
        $post->delete();

        $activity = Activity::where('event', 'trashed')->latest()->first();
        expect($activity)->not->toBeNull()
            ->and($activity->subject->is($post))->toBeTrue()
            ->and($activity->event)->toBe('trashed');

        $deletedId = $post->id;
        $post->forceDelete();

        $activity = Activity::where('event', 'deleted')->latest()->first();
        expect($activity)->not->toBeNull()
            ->and($activity->subject_type)->toBe(Post::class)
            ->and($activity->subject_id)->toBe($deletedId)
            ->and($activity->event)->toBe('deleted');
    });

    test('it logs trashed and deleted events for page', function () {
        $page = Page::factory()->create();
        $page->delete();

        $activity = Activity::where('event', 'trashed')->latest()->first();
        expect($activity)->not->toBeNull()
            ->and($activity->subject->is($page))->toBeTrue()
            ->and($activity->event)->toBe('trashed');

        $deletedId = $page->id;
        $page->forceDelete();

        $activity = Activity::where('event', 'deleted')->latest()->first();
        expect($activity)->not->toBeNull()
            ->and($activity->subject_type)->toBe(Page::class)
            ->and($activity->subject_id)->toBe($deletedId)
            ->and($activity->event)->toBe('deleted');
    });

    test('it logs trashed and deleted events for project', function () {
        $project = Project::factory()->create();
        $project->delete();

        $activity = Activity::where('event', 'trashed')->latest()->first();
        expect($activity)->not->toBeNull()
            ->and($activity->subject->is($project))->toBeTrue()
            ->and($activity->event)->toBe('trashed');

        $deletedId = $project->id;
        $project->forceDelete();

        $activity = Activity::where('event', 'deleted')->latest()->first();
        expect($activity)->not->toBeNull()
            ->and($activity->subject_type)->toBe(Project::class)
            ->and($activity->subject_id)->toBe($deletedId)
            ->and($activity->event)->toBe('deleted');
    });

    test('it logs trashed and deleted events for message', function () {
        $message = Message::factory()->create();
        $message->delete();

        $activity = Activity::where('event', 'trashed')->latest()->first();
        expect($activity)->not->toBeNull()
            ->and($activity->subject->is($message))->toBeTrue()
            ->and($activity->event)->toBe('trashed');

        $deletedId = $message->id;
        $message->forceDelete();

        $activity = Activity::where('event', 'deleted')->latest()->first();
        expect($activity)->not->toBeNull()
            ->and($activity->subject_type)->toBe(Message::class)
            ->and($activity->subject_id)->toBe($deletedId)
            ->and($activity->event)->toBe('deleted');
    });

    test('it logs trashed and deleted events for tag', function () {
        $tag = Tag::factory()->create();
        $tag->delete();

        $activity = Activity::where('event', 'trashed')->latest()->first();
        expect($activity)->not->toBeNull()
            ->and($activity->subject->is($tag))->toBeTrue()
            ->and($activity->event)->toBe('trashed');

        $deletedId = $tag->id;
        $tag->forceDelete();

        $activity = Activity::where('event', 'deleted')->latest()->first();
        expect($activity)->not->toBeNull()
            ->and($activity->subject_type)->toBe(Tag::class)
            ->and($activity->subject_id)->toBe($deletedId)
            ->and($activity->event)->toBe('deleted');
    });
});
