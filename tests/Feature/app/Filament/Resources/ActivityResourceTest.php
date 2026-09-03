<?php

/** @noinspection PhpParamsInspection, PhpPossiblePolymorphicInvocationInspection, PhpUndefinedMethodInspection, LaravelEloquentGuardedAttributeAssignmentInspection */

use App\Filament\Actions\HardDeleteAction;
use App\Filament\Resources\Activities\Pages\ListActivities;
use App\Filament\Resources\Activities\Pages\ViewActivity;
use App\Models\Activity;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'view activities',
        'delete activities',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $adminUser->assignRole($adminRole);

    $this->actingAs($adminUser);
});

it('can list activities', function () {
    // Clear any activities created during setup/boot (e.g., by LogsActivity trait on other models)
    Activity::truncate();

    $activitiesRecords = collect([
        Activity::create(['log_name' => 'default', 'description' => 'Activity One']),
        Activity::create(['log_name' => 'default', 'description' => 'Activity Two']),
        Activity::create(['log_name' => 'default', 'description' => 'Activity Three']),
    ]);

    livewire(ListActivities::class)
        ->assertOk()
        ->assertCanSeeTableRecords($activitiesRecords)
        ->assertCountTableRecords(3);
});

it('hides "activity" log name by default', function () {
    Activity::truncate();

    $visibleActivity = Activity::create(['log_name' => 'default', 'description' => 'Visible']);
    $hiddenActivity = Activity::create(['log_name' => 'activity', 'description' => 'Hidden']);

    livewire(ListActivities::class)
        ->assertCanSeeTableRecords([$visibleActivity])
        ->assertCanNotSeeTableRecords([$hiddenActivity]);
});

it('can filter by log name including "activity"', function () {
    Activity::truncate();

    $visibleActivity = Activity::create(['log_name' => 'default', 'description' => 'Visible']);
    $hiddenActivity = Activity::create(['log_name' => 'activity', 'description' => 'Hidden']);

    livewire(ListActivities::class)
        ->filterTable('log_name', 'activity')
        ->assertCanSeeTableRecords([$hiddenActivity])
        ->assertCanNotSeeTableRecords([$visibleActivity]);
});

it('sorts activities by created_at descending by default', function () {
    Activity::truncate();

    $oldest = Activity::create(['log_name' => 'default', 'description' => 'Oldest', 'created_at' => now()->subDays(2)]);
    $newest = Activity::create(['log_name' => 'default', 'description' => 'Newest', 'created_at' => now()]);
    $middle = Activity::create(['log_name' => 'default', 'description' => 'Middle', 'created_at' => now()->subDay()]);

    livewire(ListActivities::class)
        ->assertCanSeeTableRecords([$newest, $middle, $oldest], inOrder: true);
});

it('shows filter indicator for hiding activity logs only when they exist', function () {
    Activity::truncate();

    // Case 1: No 'activity' logs exist
    Activity::create(['log_name' => 'default', 'description' => 'Default Log']);
    livewire(ListActivities::class)
        ->assertDontSeeText(__("Hiding 'activity' logs"));

    // Case 2: 'activity' logs exist
    Activity::create(['log_name' => 'activity', 'description' => 'Activity Log']);
    livewire(ListActivities::class)
        ->assertSeeText(__("Hiding 'activity' logs"));
});

it('can search activities by log name', function () {
    Activity::create(['log_name' => 'UniqueLogName', 'description' => 'Description']);
    $otherActivity = Activity::create(['log_name' => 'OtherLog', 'description' => 'Other']);

    livewire(ListActivities::class)
        ->searchTable('UniqueLogName')
        ->assertCanSeeTableRecords(Activity::where('log_name', 'UniqueLogName')->get())
        ->assertCanNotSeeTableRecords([$otherActivity]);
});

it('can call the activityClean action', function () {
    livewire(ListActivities::class)
        ->callAction('activityClean')
        ->assertNotified();
});

it('hides the activityClean action when there are no activities', function () {
    Activity::truncate();

    livewire(ListActivities::class)
        ->assertActionHidden('activityClean');
});

it('displays attribute_changes and properties correctly on view page', function () {
    $activity = Activity::create([
        'log_name' => 'default',
        'description' => 'Test with changes',
        'attribute_changes' => [
            'attributes' => ['name' => 'New Name'],
            'old' => ['name' => 'Old Name'],
        ],
        'properties' => ['custom_prop' => 'custom_value'],
    ]);

    livewire(ViewActivity::class, ['record' => $activity->getRouteKey()])
        ->assertOk()
        ->assertSee('New Name')
        ->assertSee('Old Name')
        ->assertSee('custom_value');
});

it('can delete activities from the view page', function () {
    $activity = Activity::create(['log_name' => 'default', 'description' => 'Test']);

    livewire(ViewActivity::class, ['record' => $activity->getRouteKey()])
        ->callAction(HardDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertDatabaseMissing('activity_log', [
        'id' => $activity->id,
    ]);
});

it('does not crash when attribute_changes is an empty array', function () {
    $activity = Activity::create([
        'log_name' => 'default',
        'description' => 'Empty changes',
        'attribute_changes' => ['attributes' => [], 'old' => []],
        'properties' => [],
    ]);

    livewire(ViewActivity::class, ['record' => $activity->getRouteKey()])
        ->assertOk()
        ->assertSee('-');
});

it('can bulk delete activities', function () {
    $activitiesRecords = collect([
        Activity::create(['log_name' => 'Bulk One', 'description' => 'Desc']),
        Activity::create(['log_name' => 'Bulk Two', 'description' => 'Desc']),
    ]);

    livewire(ListActivities::class)
        ->callTableBulkAction('hardDeleteBulk', $activitiesRecords)
        ->assertNotified();

    foreach ($activitiesRecords as $activity) {
        $this->assertDatabaseMissing('activity_log', ['id' => $activity->id]);
    }
});

it('denies bulk delete when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view activities']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(ListActivities::class)
        ->assertTableBulkActionHidden('hardDeleteBulk');
});

it('denies listing activities when the user does not have view permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(ListActivities::class)
        ->assertForbidden();
});

it('denies loading view activities page when the user does not have view permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin']);

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $activity = Activity::create(['log_name' => 'default', 'description' => 'Test']);

    livewire(ViewActivity::class, ['record' => $activity->getRouteKey()])
        ->assertForbidden();
});

it('denies deleting activities when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view activities']);

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $activity = Activity::create(['log_name' => 'default', 'description' => 'Test']);

    livewire(ViewActivity::class, ['record' => $activity->getRouteKey()])
        ->assertActionHidden(HardDeleteAction::class);

    $this->assertDatabaseHas('activity_log', [
        'id' => $activity->id,
    ]);
});
