<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
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
        'create users',
        'view users',
        'update users',
        'delete users',
        'update roles',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $adminUser->assignRole($adminRole);

    $this->actingAs($adminUser);
});

it('can assign a role when creating a user with the right permissions', function () {
    $managerRole = Role::factory()->create(['name' => 'Manager']);

    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Assigned',
            'email' => 'role.assigned@example.com',
            'password' => 'password',
            'roles' => $managerRole->id,
            'gender' => 'unknown',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $createdUser = User::query()->where('email', 'role.assigned@example.com')->firstOrFail();

    expect($createdUser->hasRole('Manager'))->toBeTrue();
});

it('can assign a role when editing a user with the right permissions', function () {
    $noAdminRole = Role::factory()->create(['name' => 'No admin']);
    $managerRole = Role::factory()->create(['name' => 'Manager']);
    $user = User::factory()->create();
    $user->assignRole($noAdminRole);

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'roles' => $managerRole->id,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $user->refresh();

    expect($user->hasRole('Manager'))->toBeTrue()
        ->and($user->hasRole('No admin'))->toBeFalse();
});

it('does not assign a role on create when the user lacks update roles permission', function () {
    Role::factory()->create(['name' => 'Manager']);

    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'create users', 'view users']);

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'RolePermission',
            'email' => 'no.role.permission@example.com',
            'password' => 'password',
            'gender' => 'unknown',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $createdUser = User::query()->where('email', 'no.role.permission@example.com')->firstOrFail();

    expect($createdUser->hasRole('Manager'))->toBeFalse();
});

it('does not change the user role on edit when the user lacks update roles permission', function () {
    $noAdminRole = Role::factory()->create(['name' => 'No admin']);
    Role::factory()->create(['name' => 'Manager']);

    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view users', 'update users']);

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $user = User::factory()->create();
    $user->assignRole($noAdminRole);

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $user->refresh();

    expect($user->hasRole('No admin'))->toBeTrue()
        ->and($user->hasRole('Manager'))->toBeFalse();
});
