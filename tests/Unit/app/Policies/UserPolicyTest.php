<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\UserPolicy;

beforeEach(function () {
    $this->policy = new UserPolicy;

    User::factory()->create();
    Role::factory()->create(['name' => 'Admin']);

    Permission::create(['name' => 'view users']);
    Permission::create(['name' => 'create users']);
    Permission::create(['name' => 'update users']);
    Permission::create(['name' => 'delete users']);
});

test('user policy delegates inherited methods to base policy', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view users', 'create users', 'update users');

    expect($this->policy->viewAny($user))->toBeTrue()
        ->and($this->policy->view($user))->toBeTrue()
        ->and($this->policy->create($user))->toBeTrue()
        ->and($this->policy->reorder($user))->toBeTrue();
});

test('update follows hierarchical rules', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $admin->givePermissionTo('update users');

    $regularUser = User::factory()->create();
    $regularUser->givePermissionTo('update users');

    $otherUser = User::factory()->create();

    expect($this->policy->update($admin, $admin))->toBeTrue()
        ->and($this->policy->update($admin, $regularUser))->toBeTrue()
        ->and($this->policy->update($regularUser, $admin))->toBeFalse()
        ->and($this->policy->update($regularUser, $otherUser))->toBeTrue()
        ->and($this->policy->update($otherUser, $otherUser))->toBeFalse();
});

test('delete follows hierarchical rules', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $admin->givePermissionTo('delete users');

    $regularUser = User::factory()->create();
    $regularUser->givePermissionTo('delete users');

    $otherUser = User::factory()->create();

    expect($this->policy->delete($admin, $admin))->toBeTrue()
        ->and($this->policy->delete($admin, $regularUser))->toBeTrue()
        ->and($this->policy->delete($regularUser, $admin))->toBeFalse()
        ->and($this->policy->delete($regularUser, $otherUser))->toBeTrue()
        ->and($this->policy->delete($otherUser, $otherUser))->toBeFalse();
});

test('deleteAny allows users with delete users permission', function () {
    $user = User::factory()->create();
    expect($this->policy->deleteAny($user))->toBeFalse();

    $user->givePermissionTo('delete users');
    expect($this->policy->deleteAny($user))->toBeTrue();
});

test('restore and forceDelete follow delete hierarchical rules', function () {
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
        ->and($this->policy->forceDelete($admin, $admin))->toBeTrue()
        ->and($this->policy->forceDelete($admin, $regularUser))->toBeTrue()
        ->and($this->policy->forceDelete($regularUser, $admin))->toBeFalse()
        ->and($this->policy->forceDelete($regularUser, $otherUser))->toBeTrue();
});

test('restoreAny and forceDeleteAny delegate to deleteAny', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('delete users');

    $noPermUser = User::factory()->create();

    expect($this->policy->restoreAny($user))->toBeTrue()
        ->and($this->policy->restoreAny($noPermUser))->toBeFalse()
        ->and($this->policy->forceDeleteAny($user))->toBeTrue()
        ->and($this->policy->forceDeleteAny($noPermUser))->toBeFalse();
});
