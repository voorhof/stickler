<?php

/** @noinspection PhpUndefinedFieldInspection */

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Activitylog\Support\LogOptions;

uses(RefreshDatabase::class);

test('a project can be created', function () {
    $user = User::factory()->create();

    $project = Project::forceCreate([
        'title' => 'My First Project',
        'intro' => 'This is the intro of my first project.',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($project->title)->toBe('My First Project')
        ->and($project->intro)->toBe('This is the intro of my first project.')
        ->and($project->slug)->toBe('my-first-project');
});

test('a project can be mass assigned fillable values', function () {
    $project = new Project([
        'slug' => 'mass-assigned-slug',
        'title' => 'Mass Assigned Project',
        'intro' => 'Some intro text here.',
        'content' => '<p>Some <strong>rich</strong> content.</p>',
        'website_title' => 'Mass Assigned Website Title',
        'website_url' => 'Mass Assigned Website Url',
        'order_column' => 5,
        'published_at' => now(),
    ]);

    expect($project->title)->toBe('Mass Assigned Project')
        ->and($project->slug)->toBe('mass-assigned-slug')
        ->and($project->intro)->toBe('Some intro text here.')
        ->and($project->content)->toBe('<p>Some <strong>rich</strong> content.</p>')
        ->and($project->order_column)->toBe(5);
});

test('it generates a slug from title', function () {
    $project = Project::forceCreate([
        'title' => 'My Project Title',
        'intro' => 'Some intro text here.',
    ]);

    expect($project->slug)->toBe('my-project-title');
});

test('it does not regenerate a slug when updating the title', function () {
    User::factory()->create();
    $project = Project::factory()->create(['title' => 'My Project Title', 'slug' => 'my-project-title']);
    $project->update(['title' => 'My Updated Project Title']);

    expect($project->slug)->toBe('my-project-title');
});

test('it generates unique slugs for projects with the same title', function () {
    User::factory()->create();
    $firstProject = Project::factory()->create(['title' => 'Duplicate Title', 'slug' => 'duplicate-title']);
    $secondProject = Project::factory()->create(['title' => 'Duplicate Title', 'slug' => 'duplicate-title-1']);

    expect($secondProject->slug)
        ->not->toBe($firstProject->slug)
        ->and(Str::startsWith($secondProject->slug, 'duplicate-title'))->toBeTrue();
});

test('it uses slug as route key name', function () {
    expect((new Project)->getRouteKeyName())->toBe('slug');
});

test('it has a default order_column of one', function () {
    $user = User::factory()->create();

    $project = Project::forceCreate([
        'title' => 'Order Column Test',
        'intro' => 'Testing the default order column.',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($project->order_column)->toBe(1);
});

test('it can be reordered', function () {
    User::factory()->create();
    $project1 = Project::factory()->create(['order_column' => 1]);
    $project2 = Project::factory()->create(['order_column' => 2]);

    Project::swapOrder($project1, $project2);

    expect($project1->fresh()->order_column)->toBe(2)
        ->and($project2->fresh()->order_column)->toBe(1);
});

test('it automatically reorders others when order_column is manually updated', function () {
    User::factory()->create();
    $project1 = Project::factory()->create(['order_column' => 1]);
    $project2 = Project::factory()->create(['order_column' => 2]);
    $project3 = Project::factory()->create(['order_column' => 3]);

    $project1->update(['order_column' => 3]);

    expect($project1->fresh()->order_column)->toBe(3)
        ->and($project2->fresh()->order_column)->toBe(1)
        ->and($project3->fresh()->order_column)->toBe(2);
});

test('it automatically reorders others when creating a new model with a specific order_column', function () {
    $user = User::factory()->create();
    $project1 = Project::factory()->create(['order_column' => 1]);
    $project2 = Project::factory()->create(['order_column' => 2]);
    $newProject = Project::forceCreate([
        'title' => 'Content Project',
        'intro' => 'Intro text.',
        'content' => '<p>This is <strong>rich</strong> content.</p>',
        'order_column' => 1,
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($newProject->fresh()->order_column)->toBe(1)
        ->and($project1->fresh()->order_column)->toBe(2)
        ->and($project2->fresh()->order_column)->toBe(3);
});

test('it does reorder when creating a new model without specific order_column', function () {
    $user = User::factory()->create();
    $project1 = Project::factory()->create(['order_column' => 1]);
    $project2 = Project::factory()->create(['order_column' => 2]);

    $newProject = Project::forceCreate([
        'title' => 'Content Project',
        'intro' => 'Intro text.',
        'content' => '<p>This is <strong>rich</strong> content.</p>',
        // order_column will be 1 (because of SortableOnUpdate)
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($newProject->fresh()->order_column)->toBe(1)
        ->and($project1->fresh()->order_column)->toBe(2)
        ->and($project2->fresh()->order_column)->toBe(3);
});

test('it is not published when published_at is null', function () {
    User::factory()->create();
    $project = Project::factory()->create(['published_at' => null]);

    expect($project->published)->toBeFalse();
});

test('it is published when published_at is in the past', function () {
    User::factory()->create();
    $project = Project::factory()->create(['published_at' => now()->subDay()]);

    expect($project->published)->toBeTrue();
});

test('it can be soft deleted', function () {
    User::factory()->create();
    $project = Project::factory()->create();

    $project->delete();

    $this->assertSoftDeleted(Project::class, ['id' => $project->id]);
});

test('it can be restored after soft delete', function () {
    User::factory()->create();
    $project = Project::factory()->softDeleted()->create();

    $project->restore();

    $this->assertNotSoftDeleted(Project::class, ['id' => $project->id]);
});

test('it belongs to a creator user', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['created_by_user_id' => $user->id]);

    expect($project->creator)->toBeInstanceOf(User::class)
        ->and($project->creator->id)->toBe($user->id);
});

test('it belongs to an updater user', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['updated_by_user_id' => $user->id]);

    expect($project->updater)->toBeInstanceOf(User::class)
        ->and($project->updater->id)->toBe($user->id);
});

test('it returns a default user instance when creator is not loaded', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();

    $user->forceDelete();

    // withDefault() ensures the relationship always returns a User instance
    expect($project->creator)->toBeInstanceOf(User::class)
        ->and($project->creator->name)->toBe('Guest User');
});

test('it returns a default user instance when updater is not loaded', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();

    $user->forceDelete();

    // withDefault() ensures the relationship always returns a User instance
    expect($project->updater)->toBeInstanceOf(User::class)
        ->and($project->updater->name)->toBe('Guest User');
});

test('it returns rich content attributes', function () {
    $project = new Project;

    expect($project->hasRichContentAttribute('content'))->toBeTrue()
        ->and($project->hasRichContentAttribute('non_existent'))->toBeFalse();
});

test('it can attach and retrieve tags', function () {
    User::factory()->create();
    $project = Project::factory()->create();

    $project->attachTag('Design');

    expect($project->tags)->toHaveCount(1)
        ->and($project->tags->first()->name)->toBe('Design');
});

test('it casts is_published to a boolean and order_column to integer', function () {
    User::factory()->create();
    $project = Project::factory()->create([
        'published_at' => now(),
        'order_column' => '1',
    ]);

    expect($project->published)->toBeTrue()
        ->and($project->order_column)->toBe(1)->toBeInt();
});

test('it configures activity log options correctly', function () {
    $project = new Project;
    $options = $project->getActivitylogOptions();

    expect($options)->toBeInstanceOf(LogOptions::class);
});

test('content can be stored and retrieved', function () {
    $user = User::factory()->create();

    $project = Project::forceCreate([
        'title' => 'Content Project',
        'intro' => 'Intro text.',
        'content' => '<p>This is <strong>rich</strong> content.</p>',
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]);

    expect($project->content)->toBe('<p>This is <strong>rich</strong> content.</p>');
});

test('content is nullable', function () {
    User::factory()->create();
    $project = Project::factory()->create(['content' => null]);

    expect($project->content)->toBeNull();
});

test('published_at is cast to a datetime instance', function () {
    User::factory()->create();
    $project = Project::factory()->create(['published_at' => '2024-01-15 12:00:00']);

    expect($project->published_at)->toBeInstanceOf(Illuminate\Support\Carbon::class);
});

test('project implements has rich content contract', function () {
    $project = new Project;

    expect($project)->toBeInstanceOf(Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent::class);
});

test('project uses interacts with rich content and sets up rich content attributes', function () {
    $project = new Project;

    expect($project->hasRichContentAttribute('content'))->toBeTrue()
        ->and($project->getRichContentAttribute('content'))->not->toBeNull();
});
