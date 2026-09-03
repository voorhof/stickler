<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new UserPolicy;

    // Create standard roles
    User::factory()->create();
    Role::factory()->create(['name' => 'Admin']);

    // Create permissions
    Permission::create(['name' => 'view users']);
    Permission::create(['name' => 'create users']);
    Permission::create(['name' => 'update users']);
    Permission::create(['name' => 'delete users']);
});

test('viewAny allows users with view users permission', function () {
    $user = User::factory()->create();
    expect($this->policy->viewAny($user))->toBeFalse();

    $user->givePermissionTo('view users');
    expect($this->policy->viewAny($user))->toBeTrue();
});

test('view allows users with view users permission', function () {
    $user = User::factory()->create();
    expect($this->policy->view($user))->toBeFalse();

    $user->givePermissionTo('view users');
    expect($this->policy->view($user))->toBeTrue();
});

test('create allows users with create users permission', function () {
    $user = User::factory()->create();
    expect($this->policy->create($user))->toBeFalse();

    $user->givePermissionTo('create users');
    expect($this->policy->create($user))->toBeTrue();
});

test('reorder allows users with update users permission', function () {
    $user = User::factory()->create();
    expect($this->policy->reorder($user))->toBeFalse();

    $user->givePermissionTo('update users');
    expect($this->policy->reorder($user))->toBeTrue();
});

test('update follows hierarchical rules', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $admin->givePermissionTo('update users');

    $regularUser = User::factory()->create();
    $regularUser->givePermissionTo('update users');

    $otherUser = User::factory()->create();

    // Admin can update Admins and regular users
    // Regular user with permission can update others, but not Admin
    // User without permission cannot update anyone
    expect($this->policy->update($admin, $admin))->toBeTrue()
        ->and($this->policy->update($admin, $regularUser))->toBeTrue()
        ->and($this->policy->update($regularUser, $admin))->toBeFalse()
        ->and($this->policy->update($regularUser, $otherUser))->toBeTrue()
        ->and($this->policy->update($otherUser, $otherUser))->toBeFalse();
});

test('deleteAny allows users with delete users permission', function () {
    $user = User::factory()->create();
    expect($this->policy->deleteAny($user))->toBeFalse();

    $user->givePermissionTo('delete users');
    expect($this->policy->deleteAny($user))->toBeTrue();
});

test('delete follows hierarchical rules', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $admin->givePermissionTo('delete users');

    $regularUser = User::factory()->create();
    $regularUser->givePermissionTo('delete users');

    $otherUser = User::factory()->create();

    // Admin can delete Admins and regular users.
    // Regular user with permission can delete others, but not Admin
    // User without permission cannot delete anyone
    expect($this->policy->delete($admin, $admin))->toBeTrue()
        ->and($this->policy->delete($admin, $regularUser))->toBeTrue()
        ->and($this->policy->delete($regularUser, $admin))->toBeFalse()
        ->and($this->policy->delete($regularUser, $otherUser))->toBeTrue()
        ->and($this->policy->delete($otherUser, $otherUser))->toBeFalse();
});

test('restoreAny delegates to deleteAny', function () {
    $regularUser = User::factory()->create();
    $regularUser->givePermissionTo('delete users');

    $noPermUser = User::factory()->create();

    expect($this->policy->restoreAny($regularUser))->toBeTrue()
        ->and($this->policy->restoreAny($noPermUser))->toBeFalse();
});

test('restore delegates to delete with hierarchical rules', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $admin->givePermissionTo('delete users');

    $regularUser = User::factory()->create();
    $regularUser->givePermissionTo('delete users');

    $otherUser = User::factory()->create();

    expect($this->policy->restore($admin, $admin))->toBeTrue()
        ->and($this->policy->restore($admin, $regularUser))->toBeTrue()
        ->and($this->policy->restore($regularUser, $admin))->toBeFalse()
        ->and($this->policy->restore($regularUser, $otherUser))->toBeTrue()
        ->and($this->policy->restore($otherUser, $otherUser))->toBeFalse();
});

test('forceDeleteAny delegates to deleteAny', function () {
    $regularUser = User::factory()->create();
    $regularUser->givePermissionTo('delete users');

    $noPermUser = User::factory()->create();

    expect($this->policy->forceDeleteAny($regularUser))->toBeTrue()
        ->and($this->policy->forceDeleteAny($noPermUser))->toBeFalse();
});

test('forceDelete delegates to delete with hierarchical rules', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $admin->givePermissionTo('delete users');

    $regularUser = User::factory()->create();
    $regularUser->givePermissionTo('delete users');

    $otherUser = User::factory()->create();

    expect($this->policy->forceDelete($admin, $admin))->toBeTrue()
        ->and($this->policy->forceDelete($admin, $regularUser))->toBeTrue()
        ->and($this->policy->forceDelete($regularUser, $admin))->toBeFalse()
        ->and($this->policy->forceDelete($regularUser, $otherUser))->toBeTrue()
        ->and($this->policy->forceDelete($otherUser, $otherUser))->toBeFalse();
});
