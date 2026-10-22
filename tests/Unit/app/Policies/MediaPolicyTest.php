<?php

use App\Models\Permission;
use App\Models\User;
use App\Policies\MediaPolicy;

beforeEach(function () {
    $this->policy = new MediaPolicy;
});

test('media policy uses correct resource name', function () {
    Permission::create(['name' => 'view media']);

    $user = User::factory()->create();
    $user->givePermissionTo('view media');

    expect($this->policy->viewAny($user))->toBeTrue();
});

test('media policy delegates all methods to base policy', function () {
    Permission::create(['name' => 'view media']);
    Permission::create(['name' => 'create media']);
    Permission::create(['name' => 'update media']);
    Permission::create(['name' => 'delete media']);

    $user = User::factory()->create();
    $user->givePermissionTo('view media', 'create media', 'update media', 'delete media');

    expect($this->policy->viewAny($user))->toBeTrue()
        ->and($this->policy->view($user))->toBeTrue()
        ->and($this->policy->create($user))->toBeTrue()
        ->and($this->policy->update($user))->toBeTrue()
        ->and($this->policy->reorder($user))->toBeTrue()
        ->and($this->policy->deleteAny($user))->toBeTrue()
        ->and($this->policy->delete($user))->toBeTrue()
        ->and($this->policy->forceDeleteAny($user))->toBeTrue()
        ->and($this->policy->forceDelete($user))->toBeTrue();
});
