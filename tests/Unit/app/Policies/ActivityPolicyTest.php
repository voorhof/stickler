<?php

use App\Models\Permission;
use App\Models\User;
use App\Policies\ActivityPolicy;

beforeEach(function () {
    $this->policy = new ActivityPolicy;
});

test('create always returns false', function () {
    expect($this->policy->create())->toBeFalse();
});

test('update always returns false', function () {
    expect($this->policy->update())->toBeFalse();
});

test('activity policy delegates inherited methods to base policy', function () {
    Permission::create(['name' => 'view activities']);
    Permission::create(['name' => 'delete activities']);

    $user = User::factory()->create();
    $user->givePermissionTo('view activities', 'delete activities');

    expect($this->policy->viewAny($user))->toBeTrue()
        ->and($this->policy->view($user))->toBeTrue()
        ->and($this->policy->deleteAny($user))->toBeTrue()
        ->and($this->policy->delete($user))->toBeTrue()
        ->and($this->policy->forceDeleteAny($user))->toBeTrue()
        ->and($this->policy->forceDelete($user))->toBeTrue();
});
