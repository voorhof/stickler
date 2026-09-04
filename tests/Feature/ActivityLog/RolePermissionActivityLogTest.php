<?php

use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminUser = User::factory()->create();

    $adminRole = Role::factory()->create(['name' => 'Admin', 'guard_name' => 'web']);
    $permissions = collect([
        'access admin',
        'create users',
        'view users',
        'update users',
        'create roles',
        'update roles',
        'view roles',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name, 'guard_name' => 'web']));
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

test('it logs one activity with permissions when creating a role in filament', function () {
    $permission = Permission::create(['name' => 'view_dashboard', 'guard_name' => 'web']);

    livewire(CreateRole::class)
        ->fillForm([
            'name' => 'Editor',
            'guard_name' => 'web',
            'permissions' => [$permission->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $role = Role::where('name', 'Editor')->first();
    expect($role->hasPermissionTo('view_dashboard'))->toBeTrue();

    $activities = Activity::where('subject_type', $role->getMorphClass())
        ->where('subject_id', $role->id)
        ->get();

    // Should have 1 activity (created)
    expect($activities)->toHaveCount(1)
        ->and($activities->first()->event)->toBe('created');
    $permissions = $activities->first()->properties->get('permissions');
    expect($permissions)->toBeArray()->toContain('view_dashboard');
});

test('it logs one activity with permissions when updating a role in filament', function () {
    $role = Role::factory()->create(['name' => 'Editor', 'guard_name' => 'web']);
    $permission = Permission::create(['name' => 'view_dashboard', 'guard_name' => 'web']);

    livewire(EditRole::class, ['record' => $role->getRouteKey()])
        ->fillForm([
            'name' => 'EditorUpdated',
            'permissions' => [$permission->id],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $role->refresh();
    expect($role->name)->toBe('EditorUpdated')
        ->and($role->hasPermissionTo('view_dashboard'))->toBeTrue();

    $activities = Activity::where('subject_type', $role->getMorphClass())
        ->where('subject_id', $role->id)
        ->where('event', 'updated')
        ->get();

    // Should have 1 activity (updated)
    expect($activities)->toHaveCount(1);
    $permissions = $activities->first()->properties->get('permissions');
    expect($permissions)->toBeArray()->toContain('view_dashboard');
});
