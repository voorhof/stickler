<?php

/** @noinspection PhpParamsInspection, PhpUndefinedMethodInspection, PhpPossiblePolymorphicInvocationInspection */

use App\Filament\Actions\HardDeleteAction;
use App\Filament\Actions\RestoreDeletedAction;
use App\Filament\Actions\SoftDeleteAction;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'create posts',
        'view posts',
        'update posts',
        'delete posts',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('can list posts', function () {
    $posts = Post::factory()->count(3)->create();

    livewire(ListPosts::class)
        ->assertOk()
        ->assertCanSeeTableRecords($posts)
        ->assertCountTableRecords(3);
});

it('can reorder posts', function () {
    $post1 = Post::factory()->create(['order_column' => 1]);
    $post2 = Post::factory()->create(['order_column' => 2]);

    livewire(ListPosts::class)
        ->call('reorderTable', [$post2->id, $post1->id]);

    expect($post1->fresh()->order_column)->toBe(2)
        ->and($post2->fresh()->order_column)->toBe(1);
});

it('can sort posts by published', function () {
    $post1 = Post::factory()->create(['published_at' => now()->subDay()]); // Published
    $post2 = Post::factory()->create(['published_at' => null]); // Not published

    livewire(ListPosts::class)
        ->sortTable('published', 'asc')
        ->assertCanSeeTableRecords([$post2, $post1], inOrder: true);
});

it('colors the published icon by publication state', function () {
    $scheduledPost = Post::factory()->create(['published_at' => now()->addDay()]);
    $publishedPost = Post::factory()->create(['published_at' => now()->subDay()]);
    $unpublishedPost = Post::factory()->create(['published_at' => null]);

    $column = livewire(ListPosts::class)
        ->instance()
        ->getTable()
        ->getColumn('published');

    expect($column->record($scheduledPost)->getColor($scheduledPost->published))->toBe('warning')
        ->and($column->record($publishedPost)->getColor($publishedPost->published))->toBe('success')
        ->and($column->record($unpublishedPost)->getColor($unpublishedPost->published))->toBe('danger');
});

it('can search posts by title', function () {
    Post::factory()->create(['title' => 'Unique Post Title', 'slug' => 'unique-post-title']);
    $otherPost = Post::factory()->create(['title' => 'Something Else', 'slug' => 'something-else']);

    livewire(ListPosts::class)
        ->searchTable('Unique Post Title')
        ->assertCanSeeTableRecords(Post::where('title', 'Unique Post Title')->get())
        ->assertCanNotSeeTableRecords([$otherPost]);
});

it('can filter posts by trashed state on the list page table', function () {
    $activePost = Post::factory()->create();
    $trashedPost = Post::factory()->softDeleted()->create();

    livewire(ListPosts::class)
        ->filterTable('trashed', false)
        ->assertCanSeeTableRecords([$trashedPost])
        ->assertCanNotSeeTableRecords([$activePost])
        ->filterTable('trashed', true)
        ->assertCanSeeTableRecords([$activePost, $trashedPost]);
});

it('validates required fields when creating a post', function () {
    livewire(CreatePost::class)
        ->fillForm([
            'title' => null,
            'intro' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'title' => 'required',
            'intro' => 'required',
        ])
        ->assertNotNotified();
});

it('validates max lengths when creating a post', function () {
    livewire(CreatePost::class)
        ->fillForm([
            'title' => str_repeat('a', 129),
            'intro' => str_repeat('a', 513),
        ])
        ->call('create')
        ->assertHasFormErrors([
            'title' => 'max',
            'intro' => 'max',
        ])
        ->assertNotNotified();
});

it('can create a post', function () {
    $file = UploadedFile::fake()->image('cover.jpg');

    livewire(CreatePost::class)
        ->fillForm([
            'title' => 'My New Post',
            'intro' => 'This is the intro for my new post.',
            'content' => '<p>This is the <strong>rich text</strong> content.</p>',
            'cover' => $file,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(Post::class, [
        'title' => 'My New Post',
        'intro' => 'This is the intro for my new post.',
        'content' => '<p>This is the <strong>rich text</strong> content.</p>',
    ]);

    $post = Post::where('title', 'My New Post')->first();
    expect($post->getFirstMedia('cover'))->not->toBeNull();
});

it('validates required fields when editing a post', function () {
    $post = Post::factory()->create();

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'title' => null,
            'intro' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'title' => 'required',
            'intro' => 'required',
        ])
        ->assertNotNotified();
});

it('can edit a post', function () {
    $post = Post::factory()->create(['content' => '<p>Original content.</p>']);

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertFormFieldExists('content')
        ->assertSchemaStateSet(['content' => '<p>Original content.</p>'])
        ->fillForm([
            'title' => 'Updated Post Title',
            'intro' => 'Updated intro text for the post.',
            'content' => '<p>Updated <strong>rich text</strong> content.</p>',
            'slug' => 'updated-slug',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(Post::class, [
        'id' => $post->id,
        'title' => 'Updated Post Title',
        'intro' => 'Updated intro text for the post.',
        'content' => '<p>Updated <strong>rich text</strong> content.</p>',
        'slug' => 'updated-slug',
    ]);
});

it('logs tags in activity after creating a post', function () {
    $tag1 = App\Models\Tag::factory()->create(['name' => 'Tag 1']);

    livewire(CreatePost::class)
        ->fillForm([
            'title' => 'My New Post',
            'intro' => 'This is the intro.',
            'tags' => [$tag1->name],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $post = Post::where('title', 'My New Post')->first();
    $activity = Spatie\Activitylog\Models\Activity::where('subject_type', Post::class)
        ->where('subject_id', $post->id)
        ->where('event', 'created')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('tags'))->toBe(['Tag 1']);
});

it('logs tags in activity after editing a post', function () {
    $tag1 = App\Models\Tag::factory()->create(['name' => 'Tag 1']);

    $post = Post::factory()->create();
    $post->attachTag($tag1->name);

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'tags' => [],
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $activity = Spatie\Activitylog\Models\Activity::where('subject_type', Post::class)
        ->where('subject_id', $post->id)
        ->where('event', 'updated')
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('tags'))->toBe([])
        ->and($activity->properties->get('old_tags'))->toBe(['Tag 1']);
});

it('can soft delete a post', function () {
    $post = Post::factory()->create();

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->callAction(SoftDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertSoftDeleted(Post::class, ['id' => $post->id]);
});

it('can force delete a soft-deleted post', function () {
    $post = Post::factory()->softDeleted()->create();

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->callAction(HardDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertDatabaseMissing(Post::class, ['id' => $post->id]);
});

it('can restore a soft-deleted post', function () {
    $post = Post::factory()->softDeleted()->create();

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->callAction(RestoreDeletedAction::class)
        ->assertNotified();

    $this->assertNotSoftDeleted(Post::class, ['id' => $post->id]);
});

it('can bulk delete posts', function () {
    $posts = Post::factory()->count(2)->create();

    livewire(ListPosts::class)
        ->callTableBulkAction('softDeleteBulk', $posts)
        ->assertNotified();

    foreach ($posts as $post) {
        $this->assertSoftDeleted(Post::class, ['id' => $post->id]);
    }
});

it('can bulk force delete soft-deleted posts', function () {
    $posts = Post::factory()->count(2)->softDeleted()->create();

    livewire(ListPosts::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('hardDeleteBulk', $posts)
        ->assertNotified();

    foreach ($posts as $post) {
        $this->assertDatabaseMissing(Post::class, ['id' => $post->id]);
    }
});

it('can bulk restore soft-deleted posts', function () {
    $posts = Post::factory()->count(2)->softDeleted()->create();

    livewire(ListPosts::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('restoreDeletedBulk', $posts)
        ->assertNotified();

    foreach ($posts as $post) {
        $this->assertNotSoftDeleted(Post::class, ['id' => $post->id]);
    }
});

it('denies destructive bulk actions when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view posts']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $activePost = Post::factory()->create();
    $trashedPost = Post::factory()->softDeleted()->create();

    livewire(ListPosts::class)
        ->assertTableBulkActionHidden('softDeleteBulk');

    livewire(ListPosts::class)
        ->filterTable('trashed', false)
        ->assertTableBulkActionHidden('restoreDeletedBulk')
        ->assertTableBulkActionHidden('hardDeleteBulk');

    $this->assertDatabaseHas(Post::class, ['id' => $activePost->id, 'deleted_at' => null]);
    $this->assertSoftDeleted(Post::class, ['id' => $trashedPost->id]);
});

it('denies listing posts when the user does not have view permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(ListPosts::class)
        ->assertForbidden();
});

it('denies loading create post page when the user does not have create permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(CreatePost::class)
        ->assertForbidden();
});

it('denies loading edit post page when the user does not have update permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view posts']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $post = Post::factory()->create();

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertForbidden();
});

it('denies deleting a post when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view posts', 'update posts']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $post = Post::factory()->create();

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertActionHidden(SoftDeleteAction::class);

    $this->assertDatabaseHas(Post::class, ['id' => $post->id]);
});
