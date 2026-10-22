<?php

use App\Models\Permission;
use App\Models\User;
use App\Policies\ProjectPolicy;

beforeEach(function () {
    $this->policy = new ProjectPolicy;
});

test('project policy delegates all methods to base policy', function () {
    Permission::create(['name' => 'view projects']);
    Permission::create(['name' => 'create projects']);
    Permission::create(['name' => 'update projects']);
    Permission::create(['name' => 'delete projects']);

    $user = User::factory()->create();
    $user->givePermissionTo('view projects', 'create projects', 'update projects', 'delete projects');

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
