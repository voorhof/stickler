<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\RolePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new RolePolicy;
});

test('viewAny allows users with view roles permission', function () {
    Permission::create(['name' => 'view roles']);
    $user = User::factory()->create();
    $user->givePermissionTo('view roles');

    expect($this->policy->viewAny($user))->toBeTrue();
});

test('viewAny forbids users without view roles permission', function () {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse();
});

test('view allows users with view roles permission for other roles', function () {
    Permission::create(['name' => 'view roles']);
    $user = User::factory()->create();
    $user->givePermissionTo('view roles');

    expect($this->policy->view($user))->toBeTrue();
});

test('view forbids users without view roles permission', function () {
    $user = User::factory()->create();

    expect($this->policy->view($user))->toBeFalse();
});

test('create allows users with create roles permission', function () {
    Permission::create(['name' => 'create roles']);
    $user = User::factory()->create();
    $user->givePermissionTo('create roles');

    expect($this->policy->create($user))->toBeTrue();
});

test('create forbids users without create roles permission', function () {
    $user = User::factory()->create();

    expect($this->policy->create($user))->toBeFalse();
});

test('update allows Admin users to update Admin role', function () {
    $admin = User::factory()->create();
    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $admin->assignRole('Admin');

    expect($this->policy->update($admin, $adminRole))->toBeTrue();
});

test('update forbids others from updating Admin role', function () {
    $user = User::factory()->create();
    Permission::create(['name' => 'update roles']);
    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $user->givePermissionTo('update roles');

    expect($this->policy->update($user, $adminRole))->toBeFalse();
});

test('update allows users with update roles permission for other roles', function () {
    Permission::create(['name' => 'update roles']);
    $user = User::factory()->create();
    $user->givePermissionTo('update roles');
    $role = Role::factory()->create(['name' => 'Editor']);

    expect($this->policy->update($user, $role))->toBeTrue();
});

test('update forbids users without update roles permission', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create();

    expect($this->policy->update($user, $role))->toBeFalse();
});

test('reorder allows users with update roles permission', function () {
    Permission::create(['name' => 'update roles']);
    $user = User::factory()->create();
    $user->givePermissionTo('update roles');

    expect($this->policy->reorder($user))->toBeTrue();
});

test('reorder forbids users without update roles permission', function () {
    $user = User::factory()->create();

    expect($this->policy->reorder($user))->toBeFalse();
});

test('deleteAny allows users with delete roles permission', function () {
    Permission::create(['name' => 'delete roles']);
    $user = User::factory()->create();

    expect($this->policy->deleteAny($user))->toBeFalse();

    $user->givePermissionTo('delete roles');

    expect($this->policy->deleteAny($user))->toBeTrue();
});

test('delete forbids to delete Admin role', function () {
    $user = User::factory()->create();
    Permission::create(['name' => 'delete roles']);
    $adminRole = Role::factory()->create(['name' => 'Admin']);

    $user->givePermissionTo('delete roles');

    expect($this->policy->delete($user, $adminRole))->toBeFalse();
});

test('delete allows users with delete roles permission for other roles', function () {
    Permission::create(['name' => 'delete roles']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete roles');
    $role = Role::factory()->create(['name' => 'Editor']);

    expect($this->policy->delete($user, $role))->toBeTrue();
});

test('delete forbids users without delete roles permission', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create();

    expect($this->policy->delete($user, $role))->toBeFalse();
});

test('restoreAny delegates to deleteAny and allows users with delete roles permission', function () {
    Permission::create(['name' => 'delete roles']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete roles');

    expect($this->policy->restoreAny($user))->toBeTrue();
});

test('restoreAny delegates to deleteAny and forbids users without delete roles permission', function () {
    $user = User::factory()->create();

    expect($this->policy->restoreAny($user))->toBeFalse();
});

test('restore delegates to delete and forbids restoring Admin role', function () {
    $user = User::factory()->create();
    Permission::create(['name' => 'delete roles']);
    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $user->givePermissionTo('delete roles');

    expect($this->policy->restore($user, $adminRole))->toBeFalse();
});

test('restore delegates to delete and allows users with delete roles permission for other roles', function () {
    Permission::create(['name' => 'delete roles']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete roles');
    $role = Role::factory()->create(['name' => 'Editor']);

    expect($this->policy->restore($user, $role))->toBeTrue();
});

test('restore delegates to delete and forbids users without delete roles permission', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create();

    expect($this->policy->restore($user, $role))->toBeFalse();
});

test('forceDeleteAny delegates to deleteAny and allows users with delete roles permission', function () {
    Permission::create(['name' => 'delete roles']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete roles');

    expect($this->policy->forceDeleteAny($user))->toBeTrue();
});

test('forceDeleteAny delegates to deleteAny and forbids users without delete roles permission', function () {
    $user = User::factory()->create();

    expect($this->policy->forceDeleteAny($user))->toBeFalse();
});

test('forceDelete delegates to delete and forbids force deleting Admin role', function () {
    $user = User::factory()->create();
    Permission::create(['name' => 'delete roles']);
    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $user->givePermissionTo('delete roles');

    expect($this->policy->forceDelete($user, $adminRole))->toBeFalse();
});

test('forceDelete delegates to delete and allows users with delete roles permission for other roles', function () {
    Permission::create(['name' => 'delete roles']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete roles');
    $role = Role::factory()->create(['name' => 'Editor']);

    expect($this->policy->forceDelete($user, $role))->toBeTrue();
});

test('forceDelete delegates to delete and forbids users without delete roles permission', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create();

    expect($this->policy->forceDelete($user, $role))->toBeFalse();
});
