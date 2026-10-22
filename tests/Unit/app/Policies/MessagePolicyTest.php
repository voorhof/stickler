<?php

use App\Models\Permission;
use App\Models\User;
use App\Policies\MessagePolicy;

beforeEach(function () {
    $this->policy = new MessagePolicy;
});

test('create allows all users to create messages', function () {
    expect($this->policy->create())->toBeTrue();
});

test('message policy delegates inherited methods to base policy', function () {
    Permission::create(['name' => 'view messages']);
    Permission::create(['name' => 'update messages']);
    Permission::create(['name' => 'delete messages']);

    $user = User::factory()->create();
    $user->givePermissionTo('view messages', 'update messages', 'delete messages');

    expect($this->policy->viewAny($user))->toBeTrue()
        ->and($this->policy->view($user))->toBeTrue()
        ->and($this->policy->update($user))->toBeTrue()
        ->and($this->policy->reorder($user))->toBeTrue()
        ->and($this->policy->deleteAny($user))->toBeTrue()
        ->and($this->policy->delete($user))->toBeTrue()
        ->and($this->policy->restoreAny($user))->toBeTrue()
        ->and($this->policy->restore($user))->toBeTrue()
        ->and($this->policy->forceDeleteAny($user))->toBeTrue()
        ->and($this->policy->forceDelete($user))->toBeTrue();
});
