<?php

use App\Models\Permission;
use App\Models\User;
use App\Policies\MediaPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new MediaPolicy;

    Permission::create(['name' => 'view media']);
    Permission::create(['name' => 'create media']);
    Permission::create(['name' => 'update media']);
    Permission::create(['name' => 'delete media']);
});

test('viewAny allows users with view media permission', function () {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse();

    $user->givePermissionTo('view media');

    expect($this->policy->viewAny($user))->toBeTrue();
});

test('view follows viewAny permission rule', function () {
    $user = User::factory()->create();

    expect($this->policy->view($user))->toBeFalse();

    $user->givePermissionTo('view media');

    expect($this->policy->view($user))->toBeTrue();
});

test('create allows users with create media permission', function () {
    $user = User::factory()->create();

    expect($this->policy->create($user))->toBeFalse();

    $user->givePermissionTo('create media');

    expect($this->policy->create($user))->toBeTrue();
});

test('update allows users with update media permission', function () {
    $user = User::factory()->create();

    expect($this->policy->update($user))->toBeFalse();

    $user->givePermissionTo('update media');

    expect($this->policy->update($user))->toBeTrue();
});

test('reorder allows users with update media permission', function () {
    $user = User::factory()->create();

    expect($this->policy->reorder($user))->toBeFalse();

    $user->givePermissionTo('update media');

    expect($this->policy->reorder($user))->toBeTrue();
});

test('deleteAny allows users with delete media permission', function () {
    $user = User::factory()->create();

    expect($this->policy->deleteAny($user))->toBeFalse();

    $user->givePermissionTo('delete media');

    expect($this->policy->deleteAny($user))->toBeTrue();
});

test('delete allows users with delete media permission', function () {
    $user = User::factory()->create();

    expect($this->policy->delete($user))->toBeFalse();

    $user->givePermissionTo('delete media');

    expect($this->policy->delete($user))->toBeTrue();
});

test('forceDeleteAny allows users with delete media permission', function () {
    $user = User::factory()->create();

    expect($this->policy->forceDeleteAny($user))->toBeFalse();

    $user->givePermissionTo('delete media');

    expect($this->policy->forceDeleteAny($user))->toBeTrue();
});

test('forceDelete allows users with delete media permission', function () {
    $user = User::factory()->create();

    expect($this->policy->forceDelete($user))->toBeFalse();

    $user->givePermissionTo('delete media');

    expect($this->policy->forceDelete($user))->toBeTrue();
});
