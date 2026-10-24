<?php

use App\Models\Permission;
use App\Models\User;
use App\Policies\PostPolicy;

beforeEach(function () {
    $this->policy = new PostPolicy;
});

test('post policy delegates all methods to base policy', function () {
    Permission::create(['name' => 'view posts']);
    Permission::create(['name' => 'create posts']);
    Permission::create(['name' => 'update posts']);
    Permission::create(['name' => 'delete posts']);

    $user = User::factory()->create();
    $user->givePermissionTo('view posts', 'create posts', 'update posts', 'delete posts');

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
