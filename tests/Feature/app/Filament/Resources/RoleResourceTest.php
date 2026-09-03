<?php

/** @noinspection PhpUndefinedMethodInspection, PhpParamsInspection */

use App\Filament\Actions\HardDeleteAction;
use App\Filament\Actions\RestoreDeletedAction;
use App\Filament\Actions\SoftDeleteAction;
use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
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
        'create roles',
        'view roles',
        'update roles',
        'delete roles',
        'create users',
        'view users',
        'update users',
        'delete users',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $adminUser->assignRole($adminRole);

    $this->actingAs($adminUser);
});

it('can list roles', function () {
    $roles = Role::factory()->count(3)->create();

    livewire(ListRoles::class)
        ->assertOk()
        ->assertCanSeeTableRecords($roles)
        ->assertCountTableRecords(4); // 3 newly created roles + admin role from setup
});

it('can search roles by name', function () {
    Role::factory()->create(['name' => 'UniqueRoleName']);
    $otherRole = Role::factory()->create(['name' => 'Something Else']);

    livewire(ListRoles::class)
        ->searchTable('UniqueRoleName')
        ->assertCanSeeTableRecords(Role::where('name', 'UniqueRoleName')->get())
        ->assertCanNotSeeTableRecords([$otherRole]);
});

it('validates required fields when creating a role', function () {
    livewire(CreateRole::class)
        ->fillForm([
            'name' => null,
            'guard_name' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'guard_name' => 'required',
        ])
        ->assertNotNotified();
});

it('validates role name is unique', function () {
    Role::factory()->create(['name' => 'Existing Role']);

    livewire(CreateRole::class)
        ->fillForm([
            'name' => 'Existing Role',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique']);
});

it('can filter roles by trashed state on the list page table', function () {
    $activeRole = Role::factory()->create();
    $trashedRole = Role::factory()->softDeleted()->create();

    livewire(ListRoles::class)
        ->filterTable('trashed', false)
        ->assertCanSeeTableRecords([$trashedRole])
        ->assertCanNotSeeTableRecords([$activeRole])
        ->filterTable('trashed', true)
        ->assertCanSeeTableRecords([$activeRole, $trashedRole]);
});

it('can create a role', function () {
    livewire(CreateRole::class)
        ->fillForm([
            'name' => 'Manager',
            'permissions' => [],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(Role::class, [
        'name' => 'Manager',
        'guard_name' => 'web',
    ]);
});

it('validates unique name when editing a role', function () {
    $role = Role::factory()->create(['name' => 'Manager']);
    $otherRole = Role::factory()->create(['name' => 'No admin']);

    livewire(EditRole::class, ['record' => $role->getRouteKey()])
        ->fillForm([
            'name' => $otherRole->name,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'name' => 'unique',
        ])
        ->assertNotNotified();
});

it('can edit a role', function () {
    $role = Role::factory()->create(['name' => 'Manager']);

    livewire(EditRole::class, ['record' => $role->getRouteKey()])
        ->fillForm([
            'name' => 'Coach',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(Role::class, [
        'id' => $role->id,
        'name' => 'Coach',
    ]);
});

it('can soft delete a role', function () {
    $role = Role::factory()->create();

    livewire(EditRole::class, ['record' => $role->getRouteKey()])
        ->callAction(SoftDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertSoftDeleted(Role::class, ['id' => $role->id]);
});

it('can force delete a soft-deleted role', function () {
    $role = Role::factory()->softDeleted()->create();

    livewire(EditRole::class, ['record' => $role->getRouteKey()])
        ->callAction(HardDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertDatabaseMissing(Role::class, ['id' => $role->id]);
});

it('can restore a soft-deleted role', function () {
    $role = Role::factory()->softDeleted()->create();

    livewire(EditRole::class, ['record' => $role->getRouteKey()])
        ->callAction(RestoreDeletedAction::class)
        ->assertNotified();

    $this->assertNotSoftDeleted(Role::class, ['id' => $role->id]);
});

it('can bulk force delete soft-deleted roles', function () {
    $roles = Role::factory()->count(2)->softDeleted()->create();

    livewire(ListRoles::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('hardDeleteBulk', $roles)
        ->assertNotified();

    foreach ($roles as $role) {
        $this->assertDatabaseMissing(Role::class, ['id' => $role->id]);
    }
});

it('can bulk restore soft-deleted roles', function () {
    $roles = Role::factory()->count(2)->softDeleted()->create();

    livewire(ListRoles::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('restoreDeletedBulk', $roles)
        ->assertNotified();

    foreach ($roles as $role) {
        $this->assertNotSoftDeleted(Role::class, ['id' => $role->id]);
    }
});

it('denies updating the admin role for users that are not admin or super admin', function () {
    $staffRole = Role::factory()->create(['name' => 'Staff']);
    $staffRole->givePermissionTo(['access admin', 'view roles', 'update roles']);

    $staffUser = User::factory()->create();
    $staffUser->assignRole($staffRole);
    $this->actingAs($staffUser);

    $adminRole = Role::query()->where('name', 'Admin')->firstOrFail();

    livewire(EditRole::class, ['record' => $adminRole->getRouteKey()])
        ->assertForbidden();
});

it('denies destructive bulk actions when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view roles']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $activeRole = Role::factory()->create();
    $trashedRole = Role::factory()->softDeleted()->create();

    livewire(ListRoles::class)
        ->assertTableBulkActionHidden('softDeleteBulk');

    livewire(ListRoles::class)
        ->filterTable('trashed', false)
        ->assertTableBulkActionHidden('restoreDeletedBulk')
        ->assertTableBulkActionHidden('hardDeleteBulk');

    $this->assertDatabaseHas(Role::class, ['id' => $activeRole->id, 'deleted_at' => null]);
    $this->assertSoftDeleted(Role::class, ['id' => $trashedRole->id]);
});

it('denies listing roles when the user does not have view permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(ListRoles::class)
        ->assertForbidden();
});

it('denies loading create role page when the user does not have create permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(CreateRole::class)
        ->assertForbidden();
});

it('denies loading edit role page when the user does not have update permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $role = Role::factory()->create(['name' => 'Manager']);

    livewire(EditRole::class, ['record' => $role->getRouteKey()])
        ->assertForbidden();
});

it('denies deleting a role when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view roles', 'update roles']);

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $role = Role::factory()->create(['name' => 'Manager']);

    livewire(EditRole::class, ['record' => $role->getRouteKey()])
        ->assertActionHidden(SoftDeleteAction::class);

    $this->assertDatabaseHas(Role::class, [
        'id' => $role->id,
    ]);
});

it('denies bulk delete when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view roles']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(ListRoles::class)
        ->assertTableBulkActionHidden('softDeleteBulk');
});

it('hides linked models sections when tag has no linked models', function () {
    $role = Role::factory()->create(['name' => 'Role']);

    livewire(EditRole::class, ['record' => $role->getRouteKey()])
        ->assertDontSee(__('Linked :model', ['model' => __('Users')]));
});

it('displays linked users section when tag has posts', function () {
    $role = Role::factory()->create(['name' => 'Role']);
    $user = User::factory()->create(['name' => 'Role User']);
    $user->assignRole($role);

    livewire(EditRole::class, ['record' => $role->getRouteKey()])
        ->assertSee(__('Linked :model', ['model' => __('Users')]))
        ->assertSee('Role User');
});
