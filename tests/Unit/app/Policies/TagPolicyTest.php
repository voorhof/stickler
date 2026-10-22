<?php

use App\Models\Permission;
use App\Models\User;
use App\Policies\TagPolicy;

beforeEach(function () {
    $this->policy = new TagPolicy;
});

test('tag policy delegates all methods to base policy', function () {
    Permission::create(['name' => 'view tags']);
    Permission::create(['name' => 'create tags']);
    Permission::create(['name' => 'update tags']);
    Permission::create(['name' => 'delete tags']);

    $user = User::factory()->create();
    $user->givePermissionTo('view tags', 'create tags', 'update tags', 'delete tags');

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
