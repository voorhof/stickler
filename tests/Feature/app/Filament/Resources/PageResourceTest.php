<?php

/** @noinspection PhpParamsInspection, PhpUndefinedMethodInspection, PhpPossiblePolymorphicInvocationInspection */

use App\Filament\Actions\HardDeleteAction;
use App\Filament\Actions\RestoreDeletedAction;
use App\Filament\Actions\SoftDeleteAction;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Filament\RichEditor\Plugins\SourceCodePlugin;
use App\Models\Page;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;

use function Pest\Livewire\livewire;

use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'create pages',
        'view pages',
        'update pages',
        'delete pages',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('can list pages', function () {
    $pages = Page::factory()->count(3)->create();

    livewire(ListPages::class)
        ->assertOk()
        ->assertCanSeeTableRecords($pages)
        ->assertCountTableRecords(3);
});

it('can reorder pages', function () {
    $page1 = Page::factory()->create(['order_column' => 1]);
    $page2 = Page::factory()->create(['order_column' => 2]);

    livewire(ListPages::class)
        ->call('reorderTable', [$page2->id, $page1->id]);

    expect($page1->fresh()->order_column)->toBe(2)
        ->and($page2->fresh()->order_column)->toBe(1);
});

it('can sort pages by published', function () {
    $page1 = Page::factory()->create(['published_at' => now()->subDay()]); // Published
    $page2 = Page::factory()->create(['published_at' => null]); // Not published

    livewire(ListPages::class)
        ->sortTable('published', 'asc')
        ->assertCanSeeTableRecords([$page2, $page1], inOrder: true);
});

it('colors the published icon by publication state', function () {
    $scheduledPage = Page::factory()->create(['published_at' => now()->addDay()]);
    $publishedPage = Page::factory()->create(['published_at' => now()->subDay()]);
    $unpublishedPage = Page::factory()->create(['published_at' => null]);

    $column = livewire(ListPages::class)
        ->instance()
        ->getTable()
        ->getColumn('published');

    expect($column->record($scheduledPage)->getColor($scheduledPage->published))->toBe('warning')
        ->and($column->record($publishedPage)->getColor($publishedPage->published))->toBe('success')
        ->and($column->record($unpublishedPage)->getColor($unpublishedPage->published))->toBe('danger');
});

it('can search pages by title', function () {
    Page::factory()->create(['title' => 'Unique Page Title', 'slug' => 'unique-page-title']);
    $otherPage = Page::factory()->create(['title' => 'Something Else', 'slug' => 'something-else']);

    livewire(ListPages::class)
        ->searchTable('Unique Page Title')
        ->assertCanSeeTableRecords(Page::where('title', 'Unique Page Title')->get())
        ->assertCanNotSeeTableRecords([$otherPage]);
});

it('can filter pages by trashed state on the list page table', function () {
    $activePage = Page::factory()->create();
    $trashedPage = Page::factory()->softDeleted()->create();

    livewire(ListPages::class)
        ->filterTable('trashed', false)
        ->assertCanSeeTableRecords([$trashedPage])
        ->assertCanNotSeeTableRecords([$activePage])
        ->filterTable('trashed', true)
        ->assertCanSeeTableRecords([$activePage, $trashedPage]);
});

it('validates required fields when creating a page', function () {
    livewire(CreatePage::class)
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

it('validates max lengths when creating a page', function () {
    livewire(CreatePage::class)
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

it('can create a page', function () {
    $file = UploadedFile::fake()->image('cover.jpg');

    livewire(CreatePage::class)
        ->fillForm([
            'title' => 'My New Page',
            'intro' => 'This is the intro for my new page.',
            'content' => '<p>This is the <strong>rich text</strong> content.</p>',
            'cover' => $file,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(Page::class, [
        'title' => 'My New Page',
        'intro' => 'This is the intro for my new page.',
        'content' => '<p>This is the <strong>rich text</strong> content.</p>',
    ]);

    $page = Page::where('title', 'My New Page')->first();
    expect($page->getFirstMedia('cover'))->not->toBeNull();
});

it('validates required fields when editing a page', function () {
    $page = Page::factory()->create();

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
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

it('can edit a page', function () {
    $page = Page::factory()->create(['content' => '<p>Original content.</p>']);

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertFormFieldExists('content')
        ->assertSchemaStateSet(['content' => '<p>Original content.</p>'])
        ->fillForm([
            'title' => 'Updated Page Title',
            'intro' => 'Updated intro text for the page.',
            'content' => '<p>Updated <strong>rich text</strong> content.</p>',
            'slug' => 'updated-slug',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(Page::class, [
        'id' => $page->id,
        'title' => 'Updated Page Title',
        'intro' => 'Updated intro text for the page.',
        'content' => '<p>Updated <strong>rich text</strong> content.</p>',
        'slug' => 'updated-slug',
    ]);
});

it('can edit the html source code of the content', function () {
    $page = Page::factory()->create(['content' => '<p>Original content.</p>']);

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->mountAction(
            TestAction::make(SourceCodePlugin::NAME)
                ->schemaComponent('content', schema: 'form')
                ->arguments(['source' => '<h2>Title</h2><p>Original content.</p>']),
        )
        ->assertSchemaStateSet(['source' => "<h2>Title</h2>\n<p>Original content.</p>"])
        ->fillForm(['source' => '<p>Edited <strong>source</strong>.</p>'])
        ->callMountedAction()
        ->assertHasNoFormErrors()
        ->assertDispatched(
            'run-rich-editor-commands',
            fn (string $eventName, array $params): bool => $params['commands'][0]['name'] === 'setContent'
                && $params['commands'][0]['arguments'] === ['<p>Edited <strong>source</strong>.</p>'],
        );
});

it('logs tags in activity after creating a page', function () {
    $tag1 = Tag::factory()->create(['name' => 'Tag 1']);

    livewire(CreatePage::class)
        ->fillForm([
            'title' => 'My New Page',
            'intro' => 'This is the intro.',
            'tags' => [$tag1->name],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $page = Page::where('title', 'My New Page')->first();
    $activity = Activity::where('subject_type', Page::class)
        ->where('subject_id', $page->id)
        ->where('event', 'created')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('tags'))->toBe(['Tag 1']);
});

it('logs tags in activity after editing a page', function () {
    $tag1 = Tag::factory()->create(['name' => 'Tag 1']);

    $page = Page::factory()->create();
    $page->attachTag($tag1->name);

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'tags' => [],
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $activity = Activity::where('subject_type', Page::class)
        ->where('subject_id', $page->id)
        ->where('event', 'updated')
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('tags'))->toBe([])
        ->and($activity->properties->get('old_tags'))->toBe(['Tag 1']);
});

it('can soft delete a page', function () {
    $page = Page::factory()->create();

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->callAction(SoftDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertSoftDeleted(Page::class, ['id' => $page->id]);
});

it('can force delete a soft-deleted page', function () {
    $page = Page::factory()->softDeleted()->create();

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->callAction(HardDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertDatabaseMissing(Page::class, ['id' => $page->id]);
});

it('can restore a soft-deleted page', function () {
    $page = Page::factory()->softDeleted()->create();

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->callAction(RestoreDeletedAction::class)
        ->assertNotified();

    $this->assertNotSoftDeleted(Page::class, ['id' => $page->id]);
});

it('can bulk delete pages', function () {
    $pages = Page::factory()->count(2)->create();

    livewire(ListPages::class)
        ->callTableBulkAction('softDeleteBulk', $pages)
        ->assertNotified();

    foreach ($pages as $page) {
        $this->assertSoftDeleted(Page::class, ['id' => $page->id]);
    }
});

it('can bulk force delete soft-deleted pages', function () {
    $pages = Page::factory()->count(2)->softDeleted()->create();

    livewire(ListPages::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('hardDeleteBulk', $pages)
        ->assertNotified();

    foreach ($pages as $page) {
        $this->assertDatabaseMissing(Page::class, ['id' => $page->id]);
    }
});

it('can bulk restore soft-deleted pages', function () {
    $pages = Page::factory()->count(2)->softDeleted()->create();

    livewire(ListPages::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('restoreDeletedBulk', $pages)
        ->assertNotified();

    foreach ($pages as $page) {
        $this->assertNotSoftDeleted(Page::class, ['id' => $page->id]);
    }
});

it('denies destructive bulk actions when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view pages']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $activePage = Page::factory()->create();
    $trashedPage = Page::factory()->softDeleted()->create();

    livewire(ListPages::class)
        ->assertTableBulkActionHidden('softDeleteBulk');

    livewire(ListPages::class)
        ->filterTable('trashed', false)
        ->assertTableBulkActionHidden('restoreDeletedBulk')
        ->assertTableBulkActionHidden('hardDeleteBulk');

    $this->assertDatabaseHas(Page::class, ['id' => $activePage->id, 'deleted_at' => null]);
    $this->assertSoftDeleted(Page::class, ['id' => $trashedPage->id]);
});

it('denies listing pages when the user does not have view permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(ListPages::class)
        ->assertForbidden();
});

it('denies loading create page page when the user does not have create permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(CreatePage::class)
        ->assertForbidden();
});

it('denies loading edit page page when the user does not have update permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view pages']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $page = Page::factory()->create();

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertForbidden();
});

it('denies deleting a page when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view pages', 'update pages']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $page = Page::factory()->create();

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertActionHidden(SoftDeleteAction::class);

    $this->assertDatabaseHas(Page::class, ['id' => $page->id]);
});
