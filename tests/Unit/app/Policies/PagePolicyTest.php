<?php

use App\Models\Permission;
use App\Models\User;
use App\Policies\PagePolicy;

beforeEach(function () {
    $this->policy = new PagePolicy;
});

test('page policy delegates all methods to base policy', function () {
    Permission::create(['name' => 'view pages']);
    Permission::create(['name' => 'create pages']);
    Permission::create(['name' => 'update pages']);
    Permission::create(['name' => 'delete pages']);

    $user = User::factory()->create();
    $user->givePermissionTo('view pages', 'create pages', 'update pages', 'delete pages');

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
