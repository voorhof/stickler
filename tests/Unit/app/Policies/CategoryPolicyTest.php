<?php

use App\Models\Permission;
use App\Models\User;
use App\Policies\CategoryPolicy;

beforeEach(function () {
    $this->policy = new CategoryPolicy;
});

test('category policy delegates all methods to base policy', function () {
    Permission::create(['name' => 'view categories']);
    Permission::create(['name' => 'create categories']);
    Permission::create(['name' => 'update categories']);
    Permission::create(['name' => 'delete categories']);

    $user = User::factory()->create();
    $user->givePermissionTo('view categories', 'create categories', 'update categories', 'delete categories');

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
