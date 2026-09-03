<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
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
        'update roles',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name, 'guard_name' => 'web']));

    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

test('it logs one activity with roles when creating a user in filament', function () {
    $role = Role::factory()->create(['name' => 'Editor', 'guard_name' => 'web']);

    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'User',
            'email' => 'new@example.com',
            'password' => 'password',
            'roles' => $role->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::where('email', 'new@example.com')->first();
    expect($user->hasRole('Editor'))->toBeTrue();

    $activities = Activity::where('subject_type', $user->getMorphClass())
        ->where('subject_id', $user->id)
        ->get();

    // User should only have 1 activity (created)
    expect($activities)->toHaveCount(1)
        ->and($activities->first()->event)->toBe('created');
    $roles = $activities->first()->properties->get('roles');
    expect($roles)->toBeArray()->toContain('Editor');
});

test('it logs one activity with roles when updating a user in filament', function () {
    $user = User::factory()->create(['name' => 'OldName']);
    $role = Role::factory()->create(['name' => 'Editor', 'guard_name' => 'web']);

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => 'NewName',
            'roles' => $role->id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();
    expect($user->name)->toBe('Newname')
        ->and($user->hasRole('Editor'))->toBeTrue();

    $activities = Activity::where('subject_type', $user->getMorphClass())
        ->where('subject_id', $user->id)
        ->where('event', 'updated')
        ->get();

    // Should have 1 activity (updated)
    expect($activities)->toHaveCount(1);
    $roles = $activities->first()->properties->get('roles');
    expect($roles)->toBeArray()->toContain('Editor');
});

test('it logs activity when only roles are changed in filament', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'Editor', 'guard_name' => 'web']);

    // Ensure we start with no roles
    expect($user->roles)->toHaveCount(0);

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'roles' => $role->id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();
    expect($user->hasRole('Editor'))->toBeTrue();

    $activities = Activity::where('subject_type', $user->getMorphClass())
        ->where('subject_id', $user->id)
        ->where('event', 'updated')
        ->get();

    // Even if no fillable attributes changed, Filament's save action might still trigger an update if it touches the model,
    // OR we might need to verify if Spatie Activitylog logs it.
    // Actually, Spatie Activitylog dontLogEmptyChanges() might prevent it if ONLY roles (pivot) changed.

    // Let's see what happens.
    expect($activities)->not->toBeEmpty('Activity should be logged when roles change');
    $roles = $activities->first()->properties->get('roles');
    expect($roles)->toBeArray()->toContain('Editor');
});
