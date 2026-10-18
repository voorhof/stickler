<?php

/** @noinspection PhpParamsInspection, PhpUndefinedMethodInspection, PhpPossiblePolymorphicInvocationInspection */

use App\Filament\Actions\HardDeleteAction;
use App\Filament\Actions\RestoreDeletedAction;
use App\Filament\Actions\SoftDeleteAction;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Projects\Pages\ListProject;
use App\Filament\RichEditor\Plugins\SourceCodePlugin;
use App\Models\Permission;
use App\Models\Project;
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
        'create projects',
        'view projects',
        'update projects',
        'delete projects',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('can list projects', function () {
    $projects = Project::factory()->count(3)->create();

    livewire(ListProject::class)
        ->assertOk()
        ->assertCanSeeTableRecords($projects)
        ->assertCountTableRecords(3);
});

it('can reorder projects', function () {
    $project1 = Project::factory()->create(['order_column' => 1]);
    $project2 = Project::factory()->create(['order_column' => 2]);

    livewire(ListProject::class)
        ->call('reorderTable', [$project2->id, $project1->id]);

    expect($project1->fresh()->order_column)->toBe(2)
        ->and($project2->fresh()->order_column)->toBe(1);
});

it('can sort projects by published', function () {
    $project1 = Project::factory()->create(['published_at' => now()->subDay()]); // Published
    $project2 = Project::factory()->create(['published_at' => null]); // Not published

    livewire(ListProject::class)
        ->sortTable('published', 'asc')
        ->assertCanSeeTableRecords([$project2, $project1], inOrder: true);
});

it('colors the published icon by publication state', function () {
    $scheduledProject = Project::factory()->create(['published_at' => now()->addDay()]);
    $publishedProject = Project::factory()->create(['published_at' => now()->subDay()]);
    $unpublishedProject = Project::factory()->create(['published_at' => null]);

    $column = livewire(ListProject::class)
        ->instance()
        ->getTable()
        ->getColumn('published');

    expect($column->record($scheduledProject)->getColor($scheduledProject->published))->toBe('warning')
        ->and($column->record($publishedProject)->getColor($publishedProject->published))->toBe('success')
        ->and($column->record($unpublishedProject)->getColor($unpublishedProject->published))->toBe('danger');
});

it('can search projects by title', function () {
    Project::factory()->create(['title' => 'Unique Project Title', 'slug' => 'unique-project-title']);
    $otherProject = Project::factory()->create(['title' => 'Something Else', 'slug' => 'something-else']);

    livewire(ListProject::class)
        ->searchTable('Unique Project Title')
        ->assertCanSeeTableRecords(Project::where('title', 'Unique Project Title')->get())
        ->assertCanNotSeeTableRecords([$otherProject]);
});

it('can filter projects by trashed state on the list page table', function () {
    $activeProject = Project::factory()->create();
    $trashedProject = Project::factory()->softDeleted()->create();

    livewire(ListProject::class)
        ->filterTable('trashed', false)
        ->assertCanSeeTableRecords([$trashedProject])
        ->assertCanNotSeeTableRecords([$activeProject])
        ->filterTable('trashed', true)
        ->assertCanSeeTableRecords([$activeProject, $trashedProject]);
});

it('validates required fields when creating a project', function () {
    livewire(CreateProject::class)
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

it('validates max lengths when creating a project', function () {
    livewire(CreateProject::class)
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

it('can create a project', function () {
    $file = UploadedFile::fake()->image('cover.jpg');

    livewire(CreateProject::class)
        ->fillForm([
            'title' => 'My New Project',
            'intro' => 'This is the intro for my new project.',
            'content' => '<p>This is the <strong>rich text</strong> content.</p>',
            'cover' => $file,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(Project::class, [
        'title' => 'My New Project',
        'intro' => 'This is the intro for my new project.',
        'content' => '<p>This is the <strong>rich text</strong> content.</p>',
    ]);

    $project = Project::where('title', 'My New Project')->first();
    expect($project->getFirstMedia('cover'))->not->toBeNull();
});

it('validates required fields when editing a project', function () {
    $project = Project::factory()->create();

    livewire(EditProject::class, ['record' => $project->getRouteKey()])
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

it('can edit a project', function () {
    $project = Project::factory()->create(['content' => '<p>Original content.</p>']);

    livewire(EditProject::class, ['record' => $project->getRouteKey()])
        ->assertFormFieldExists('content')
        ->assertSchemaStateSet(['content' => '<p>Original content.</p>'])
        ->fillForm([
            'title' => 'Updated Project Title',
            'intro' => 'Updated intro text for the project.',
            'content' => '<p>Updated <strong>rich text</strong> content.</p>',
            'slug' => 'updated-slug',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(Project::class, [
        'id' => $project->id,
        'title' => 'Updated Project Title',
        'intro' => 'Updated intro text for the project.',
        'content' => '<p>Updated <strong>rich text</strong> content.</p>',
        'slug' => 'updated-slug',
    ]);
});

it('can edit the html source code of the content', function () {
    $project = Project::factory()->create(['content' => '<p>Original content.</p>']);

    livewire(EditProject::class, ['record' => $project->getRouteKey()])
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

it('logs tags in activity after creating a project', function () {
    $tag1 = Tag::factory()->create(['name' => 'Tag 1']);

    livewire(CreateProject::class)
        ->fillForm([
            'title' => 'My New Project',
            'intro' => 'This is the intro.',
            'tags' => [$tag1->name],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $project = Project::where('title', 'My New Project')->first();
    $activity = Activity::where('subject_type', Project::class)
        ->where('subject_id', $project->id)
        ->where('event', 'created')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('tags'))->toBe(['Tag 1']);
});

it('logs tags in activity after editing a project', function () {
    $tag1 = Tag::factory()->create(['name' => 'Tag 1']);

    $project = Project::factory()->create();
    $project->attachTag($tag1->name);

    livewire(EditProject::class, ['record' => $project->getRouteKey()])
        ->fillForm([
            'tags' => [],
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $activity = Activity::where('subject_type', Project::class)
        ->where('subject_id', $project->id)
        ->where('event', 'updated')
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('tags'))->toBe([])
        ->and($activity->properties->get('old_tags'))->toBe(['Tag 1']);
});

it('can soft delete a project', function () {
    $project = Project::factory()->create();

    livewire(EditProject::class, ['record' => $project->getRouteKey()])
        ->callAction(SoftDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertSoftDeleted(Project::class, ['id' => $project->id]);
});

it('can force delete a soft-deleted project', function () {
    $project = Project::factory()->softDeleted()->create();

    livewire(EditProject::class, ['record' => $project->getRouteKey()])
        ->callAction(HardDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertDatabaseMissing(Project::class, ['id' => $project->id]);
});

it('can restore a soft-deleted project', function () {
    $project = Project::factory()->softDeleted()->create();

    livewire(EditProject::class, ['record' => $project->getRouteKey()])
        ->callAction(RestoreDeletedAction::class)
        ->assertNotified();

    $this->assertNotSoftDeleted(Project::class, ['id' => $project->id]);
});

it('can bulk delete projects', function () {
    $projects = Project::factory()->count(2)->create();

    livewire(ListProject::class)
        ->callTableBulkAction('softDeleteBulk', $projects)
        ->assertNotified();

    foreach ($projects as $project) {
        $this->assertSoftDeleted(Project::class, ['id' => $project->id]);
    }
});

it('can bulk force delete soft-deleted projects', function () {
    $projects = Project::factory()->count(2)->softDeleted()->create();

    livewire(ListProject::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('hardDeleteBulk', $projects)
        ->assertNotified();

    foreach ($projects as $project) {
        $this->assertDatabaseMissing(Project::class, ['id' => $project->id]);
    }
});

it('can bulk restore soft-deleted projects', function () {
    $projects = Project::factory()->count(2)->softDeleted()->create();

    livewire(ListProject::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('restoreDeletedBulk', $projects)
        ->assertNotified();

    foreach ($projects as $project) {
        $this->assertNotSoftDeleted(Project::class, ['id' => $project->id]);
    }
});

it('denies destructive bulk actions when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view projects']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $activeProject = Project::factory()->create();
    $trashedProject = Project::factory()->softDeleted()->create();

    livewire(ListProject::class)
        ->assertTableBulkActionHidden('softDeleteBulk');

    livewire(ListProject::class)
        ->filterTable('trashed', false)
        ->assertTableBulkActionHidden('restoreDeletedBulk')
        ->assertTableBulkActionHidden('hardDeleteBulk');

    $this->assertDatabaseHas(Project::class, ['id' => $activeProject->id, 'deleted_at' => null]);
    $this->assertSoftDeleted(Project::class, ['id' => $trashedProject->id]);
});

it('denies listing projects when the user does not have view permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(ListProject::class)
        ->assertForbidden();
});

it('denies loading create project page when the user does not have create permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(CreateProject::class)
        ->assertForbidden();
});

it('denies loading edit project page when the user does not have update permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view projects']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $project = Project::factory()->create();

    livewire(EditProject::class, ['record' => $project->getRouteKey()])
        ->assertForbidden();
});

it('denies deleting a project when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view projects', 'update projects']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $project = Project::factory()->create();

    livewire(EditProject::class, ['record' => $project->getRouteKey()])
        ->assertActionHidden(SoftDeleteAction::class);

    $this->assertDatabaseHas(Project::class, ['id' => $project->id]);
});

it('shows the view public page action when project is published', function () {
    $project = Project::factory()->create(['published_at' => now()->subHour()]);

    livewire(EditProject::class, ['record' => $project->getRouteKey()])
        ->assertActionVisible('viewPublicPage')
        ->assertActionHasUrl('viewPublicPage', route('projects.show', $project));
});

it('hides the view public page action when project is not published', function () {
    $unpublishedProject = Project::factory()->create(['published_at' => null]);
    $scheduledProject = Project::factory()->create(['published_at' => now()->addHour()]);

    livewire(EditProject::class, ['record' => $unpublishedProject->getRouteKey()])
        ->assertActionHidden('viewPublicPage');

    livewire(EditProject::class, ['record' => $scheduledProject->getRouteKey()])
        ->assertActionHidden('viewPublicPage');
});
