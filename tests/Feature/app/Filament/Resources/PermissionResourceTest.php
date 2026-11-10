<?php

/** @noinspection PhpParamsInspection, PhpUndefinedMethodInspection */

use App\Filament\Resources\Permissions\Pages\CreatePermission;
use App\Filament\Resources\Permissions\Pages\EditPermission;
use App\Filament\Resources\Permissions\Pages\ListPermissions;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'create roles',
        'view roles',
        'update roles',
        'delete roles',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $adminUser->assignRole($adminRole);

    $this->actingAs($adminUser);
});

it('can list permissions', function () {
    $permissions = collect([
        Permission::create(['name' => 'view posts']),
        Permission::create(['name' => 'create posts']),
        Permission::create(['name' => 'edit posts']),
    ]);

    livewire(ListPermissions::class)
        ->assertOk()
        ->assertCanSeeTableRecords($permissions)
        ->assertCountTableRecords(8); // 3 newly created + 5 from setup (access admin, create/view/update/delete roles)
});

it('can search permissions by name', function () {
    Permission::create(['name' => 'UniquePermissionName']);
    $otherPermission = Permission::create(['name' => 'Something Else']);

    livewire(ListPermissions::class)
        ->searchTable('UniquePermissionName')
        ->assertCanSeeTableRecords(Permission::where('name', 'UniquePermissionName')->get())
        ->assertCanNotSeeTableRecords([$otherPermission]);
});

it('validates required fields when creating a permission', function () {
    livewire(CreatePermission::class)
        ->fillForm([
            'name' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
        ])
        ->assertNotNotified();
});

it('validates permission name is unique', function () {
    Permission::create(['name' => 'Existing Permission']);

    livewire(CreatePermission::class)
        ->fillForm([
            'name' => 'Existing Permission',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique']);
});

it('can create a permission', function () {
    livewire(CreatePermission::class)
        ->fillForm([
            'name' => 'manage settings',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(Permission::class, [
        'name' => 'manage settings',
        'guard_name' => 'web',
    ]);
});

it('validates unique name when editing a permission', function () {
    $permission = Permission::create(['name' => 'manage settings']);
    $otherPermission = Permission::create(['name' => 'view dashboard']);

    livewire(EditPermission::class, ['record' => $permission->id])
        ->fillForm([
            'name' => $otherPermission->name,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'name' => 'unique',
        ])
        ->assertNotNotified();
});

it('can edit a permission', function () {
    $permission = Permission::create(['name' => 'manage settings']);

    livewire(EditPermission::class, ['record' => $permission->id])
        ->assertSchemaStateSet([
            'name' => 'manage settings',
            'guard_name' => 'web',
        ]);
});

it('denies listing permissions when the user does not have view permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(ListPermissions::class)
        ->assertForbidden();
});

it('denies loading create permission page when the user does not have create permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(CreatePermission::class)
        ->assertForbidden();
});

it('denies loading edit permission page when the user does not have update permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $permission = Permission::create(['name' => 'manage settings']);

    livewire(EditPermission::class, ['record' => $permission->id])
        ->assertForbidden();
});
