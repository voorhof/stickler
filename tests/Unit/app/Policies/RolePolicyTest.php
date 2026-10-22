<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\RolePolicy;

beforeEach(function () {
    $this->policy = new RolePolicy;
});

test('role policy delegates inherited methods to base policy', function () {
    Permission::create(['name' => 'view roles']);
    Permission::create(['name' => 'create roles']);
    Permission::create(['name' => 'update roles']);
    Permission::create(['name' => 'delete roles']);

    $user = User::factory()->create();
    $user->givePermissionTo('view roles', 'create roles', 'update roles', 'delete roles');

    expect($this->policy->viewAny($user))->toBeTrue()
        ->and($this->policy->view($user))->toBeTrue()
        ->and($this->policy->create($user))->toBeTrue()
        ->and($this->policy->reorder($user))->toBeTrue()
        ->and($this->policy->deleteAny($user))->toBeTrue()
        ->and($this->policy->restoreAny($user))->toBeTrue()
        ->and($this->policy->forceDeleteAny($user))->toBeTrue();
});

test('update allows Admin users to update Admin role', function () {
    $admin = User::factory()->create();
    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $admin->assignRole('Admin');

    expect($this->policy->update($admin, $adminRole))->toBeTrue();
});

test('update forbids non-Admin users from updating Admin role', function () {
    Permission::create(['name' => 'update roles']);
    $user = User::factory()->create();
    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $user->givePermissionTo('update roles');

    expect($this->policy->update($user, $adminRole))->toBeFalse();
});

test('update allows users with permission for non-Admin roles', function () {
    Permission::create(['name' => 'update roles']);
    $user = User::factory()->create();
    $user->givePermissionTo('update roles');
    $role = Role::factory()->create(['name' => 'Editor']);

    expect($this->policy->update($user, $role))->toBeTrue();
});

test('delete forbids deleting Admin role', function () {
    Permission::create(['name' => 'delete roles']);
    $user = User::factory()->create();
    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $user->givePermissionTo('delete roles');

    expect($this->policy->delete($user, $adminRole))->toBeFalse();
});

test('delete allows users with permission for non-Admin roles', function () {
    Permission::create(['name' => 'delete roles']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete roles');
    $role = Role::factory()->create(['name' => 'Editor']);

    expect($this->policy->delete($user, $role))->toBeTrue();
});

test('restore and forceDelete follow delete rules', function () {
    Permission::create(['name' => 'delete roles']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete roles');

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $editorRole = Role::factory()->create(['name' => 'Editor']);

    expect($this->policy->restore($user, $adminRole))->toBeFalse()
        ->and($this->policy->restore($user, $editorRole))->toBeTrue()
        ->and($this->policy->forceDelete($user, $adminRole))->toBeFalse()
        ->and($this->policy->forceDelete($user, $editorRole))->toBeTrue();
});
