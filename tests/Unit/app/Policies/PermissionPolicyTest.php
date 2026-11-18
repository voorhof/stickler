<?php

use App\Models\Permission;
use App\Models\User;
use App\Policies\PermissionPolicy;

beforeEach(function () {
    $this->policy = new PermissionPolicy;
});

test('permission policy delegates all methods to base policy', function () {
    Permission::create(['name' => 'view roles']);
    Permission::create(['name' => 'create roles']);
    Permission::create(['name' => 'update roles']);
    Permission::create(['name' => 'delete roles']);

    $user = User::factory()->create();
    $user->givePermissionTo('view roles', 'create roles', 'update roles', 'delete roles');

    expect($this->policy->viewAny($user))->toBeTrue()
        ->and($this->policy->view($user))->toBeTrue()
        ->and($this->policy->create($user))->toBeTrue()
        ->and($this->policy->update($user))->toBeTrue()
        ->and($this->policy->reorder($user))->toBeTrue()
        ->and($this->policy->deleteAny($user))->toBeTrue()
        ->and($this->policy->delete($user))->toBeTrue()
        ->and($this->policy->restoreAny($user))->toBeTrue()
        ->and($this->policy->restore($user))->toBeTrue()
        ->and($this->policy->forceDeleteAny($user))->toBeTrue()
        ->and($this->policy->forceDelete($user))->toBeTrue();
});
