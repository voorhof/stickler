<?php

/** @noinspection PhpParamsInspection, PhpUndefinedMethodInspection, PhpPossiblePolymorphicInvocationInspection */

use App\Filament\Actions\HardDeleteAction;
use App\Filament\Actions\RestoreDeletedAction;
use App\Filament\Actions\SoftDeleteAction;
use App\Filament\Resources\Tags\Pages\CreateTag;
use App\Filament\Resources\Tags\Pages\EditTag;
use App\Filament\Resources\Tags\Pages\ListTags;
use App\Models\Media;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Project;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'create tags',
        'view tags',
        'update tags',
        'delete tags',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('can list tags', function () {
    $tags = Tag::factory()->count(3)->create();

    livewire(ListTags::class)
        ->assertOk()
        ->assertCanSeeTableRecords($tags)
        ->assertCountTableRecords(3);
});

it('can reorder tags', function () {
    $tag1 = Tag::factory()->create(['order_column' => 1]);
    $tag2 = Tag::factory()->create(['order_column' => 2]);

    livewire(ListTags::class)
        ->call('reorderTable', [$tag2->id, $tag1->id]);

    expect($tag1->fresh()->order_column)->toBe(2)
        ->and($tag2->fresh()->order_column)->toBe(1);
});

it('can search tags by name', function () {
    Tag::factory()->create(['name' => 'Unique Tag Title', 'slug' => 'unique-tag-name']);
    $otherTag = Tag::factory()->create(['name' => 'Something Else', 'slug' => 'something-else']);

    livewire(ListTags::class)
        ->searchTable('Unique Tag Title')
        ->assertCanSeeTableRecords(Tag::where('name', 'Unique Tag Title')->get())
        ->assertCanNotSeeTableRecords([$otherTag]);
});

it('can filter tags by trashed state on the list page table', function () {
    $activeTag = Tag::factory()->create();
    $trashedTag = Tag::factory()->softDeleted()->create();

    livewire(ListTags::class)
        ->filterTable('trashed', false)
        ->assertCanSeeTableRecords([$trashedTag])
        ->assertCanNotSeeTableRecords([$activeTag])
        ->filterTable('trashed', true)
        ->assertCanSeeTableRecords([$activeTag, $trashedTag]);
});

it('validates required fields when creating a tag', function () {
    livewire(CreateTag::class)
        ->fillForm([
            'name' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
        ])
        ->assertNotNotified();
});

it('validates name max length when creating a tag', function () {
    livewire(CreateTag::class)
        ->fillForm([
            'name' => str_repeat('a', 129),
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'max'])
        ->assertNotNotified();
});

it('can create a tag', function () {
    livewire(CreateTag::class)
        ->fillForm([
            'name' => 'My New Tag',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(Tag::class, [
        'name->nl_BE' => 'My New Tag',
    ]);
});

it('validates required fields when editing a tag', function () {
    $tag = Tag::factory()->create();

    livewire(EditTag::class, ['record' => $tag->getRouteKey()])
        ->fillForm([
            'name' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'name' => 'required',
        ])
        ->assertNotNotified();
});

it('can edit a tag', function () {
    $tag = Tag::factory()->create();

    livewire(EditTag::class, ['record' => $tag->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Tag Title',
            'url_slug' => 'updated-slug',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(Tag::class, [
        'id' => $tag->id,
        'name->nl_BE' => 'Updated Tag Title',
        'url_slug' => 'updated-slug',
    ]);
});

it('can soft delete a tag', function () {
    $tag = Tag::factory()->create();

    livewire(EditTag::class, ['record' => $tag->getRouteKey()])
        ->callAction(SoftDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertSoftDeleted(Tag::class, ['id' => $tag->id]);
});

it('can force delete a soft-deleted tag', function () {
    $tag = Tag::factory()->softDeleted()->create();

    livewire(EditTag::class, ['record' => $tag->getRouteKey()])
        ->callAction(HardDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertDatabaseMissing(Tag::class, ['id' => $tag->id]);
});

it('can restore a soft-deleted tag', function () {
    $tag = Tag::factory()->softDeleted()->create();

    livewire(EditTag::class, ['record' => $tag->getRouteKey()])
        ->callAction(RestoreDeletedAction::class)
        ->assertNotified();

    $this->assertNotSoftDeleted(Tag::class, ['id' => $tag->id]);
});

it('can bulk delete tags', function () {
    $tags = Tag::factory()->count(2)->create();

    livewire(ListTags::class)
        ->callTableBulkAction('softDeleteBulk', $tags)
        ->assertNotified();

    foreach ($tags as $tag) {
        $this->assertSoftDeleted(Tag::class, ['id' => $tag->id]);
    }
});

it('can bulk force delete soft-deleted tags', function () {
    $tags = Tag::factory()->count(2)->softDeleted()->create();

    livewire(ListTags::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('hardDeleteBulk', $tags)
        ->assertNotified();

    foreach ($tags as $tag) {
        $this->assertDatabaseMissing(Tag::class, ['id' => $tag->id]);
    }
});

it('can bulk restore soft-deleted tags', function () {
    $tags = Tag::factory()->count(2)->softDeleted()->create();

    livewire(ListTags::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('restoreDeletedBulk', $tags)
        ->assertNotified();

    foreach ($tags as $tag) {
        $this->assertNotSoftDeleted(Tag::class, ['id' => $tag->id]);
    }
});

it('denies destructive bulk actions when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view tags']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $activeTag = Tag::factory()->create();
    $trashedTag = Tag::factory()->softDeleted()->create();

    livewire(ListTags::class)
        ->assertTableBulkActionHidden('softDeleteBulk');

    livewire(ListTags::class)
        ->filterTable('trashed', false)
        ->assertTableBulkActionHidden('restoreDeletedBulk')
        ->assertTableBulkActionHidden('hardDeleteBulk');

    $this->assertDatabaseHas(Tag::class, ['id' => $activeTag->id, 'deleted_at' => null]);
    $this->assertSoftDeleted(Tag::class, ['id' => $trashedTag->id]);
});

it('denies listing tags when the user does not have view permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(ListTags::class)
        ->assertForbidden();
});

it('denies loading create tag page when the user does not have create permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(CreateTag::class)
        ->assertForbidden();
});

it('denies loading edit tag page when the user does not have update permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view tags']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $tag = Tag::factory()->create();

    livewire(EditTag::class, ['record' => $tag->getRouteKey()])
        ->assertForbidden();
});

it('denies deleting a tag when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view tags', 'update tags']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $tag = Tag::factory()->create();

    livewire(EditTag::class, ['record' => $tag->getRouteKey()])
        ->assertActionHidden(SoftDeleteAction::class);

    $this->assertDatabaseHas(Tag::class, ['id' => $tag->id]);
});

it('hides linked models sections when tag has no linked models', function () {
    $tag = Tag::factory()->create();

    livewire(EditTag::class, ['record' => $tag->getRouteKey()])
        ->assertDontSee(__('Linked :model', ['model' => __('Blogposts')]))
        ->assertDontSee(__('Linked :model', ['model' => __('Project')]))
        ->assertDontSee(__('Linked :model', ['model' => __('Media')]));
});

it('displays linked blogposts section when tag has posts', function () {
    $tag = Tag::factory()->create();
    $post = Post::factory()->create(['title' => 'Test Blogpost Title']);
    $tag->posts()->attach($post);

    livewire(EditTag::class, ['record' => $tag->getRouteKey()])
        ->assertSee(__('Linked :model', ['model' => __('Blogposts')]))
        ->assertSee('Test Blogpost Title');
});

it('displays linked projects section when tag has projects', function () {
    $tag = Tag::factory()->create();
    $project = Project::factory()->create(['title' => 'Test Project Title']);
    $tag->projects()->attach($project);

    livewire(EditTag::class, ['record' => $tag->getRouteKey()])
        ->assertSee(__('Linked :model', ['model' => __('Projects')]))
        ->assertSee('Test Project Title');
});

it('displays linked media section when tag has media', function () {
    $tag = Tag::factory()->create();
    $user = User::factory()->create();
    $media = Media::query()->create([
        'model_type' => User::class,
        'model_id' => $user->id,
        'uuid' => (string) str()->uuid(),
        'collection_name' => 'avatar',
        'name' => 'Test Media Name',
        'file_name' => 'test-media.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'conversions_disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
        'order_column' => 1,
    ]);
    $tag->media()->attach($media);

    livewire(EditTag::class, ['record' => $tag->getRouteKey()])
        ->assertSee(__('Linked :model', ['model' => __('Media')]))
        ->assertSee('Test Media Name');
});
