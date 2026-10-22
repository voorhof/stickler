<?php

use App\Models\Permission;
use App\Models\User;
use App\Policies\BasePolicy;
use App\Policies\MediaPolicy;
use App\Policies\PostPolicy;

test('base policy resolves resource name from class name', function () {
    $policy = new PostPolicy;

    $resourceName = (function () {
        return $this->resourceName();
    })->call($policy);

    expect($resourceName)->toBe('posts');
});

test('base policy resolves media resource name correctly', function () {
    $policy = new MediaPolicy;

    $resourceName = (function () {
        return $this->resourceName();
    })->call($policy);

    expect($resourceName)->toBe('media');
});

test('base policy builds permission name correctly', function () {
    $policy = new PostPolicy;

    $permissionName = (function () {
        return $this->permissionName('view');
    })->call($policy);

    expect($permissionName)->toBe('view posts');
});

test('viewAny checks the correct permission', function () {
    Permission::create(['name' => 'view posts']);
    $user = User::factory()->create();
    $user->givePermissionTo('view posts');

    $policy = new PostPolicy;

    expect($policy->viewAny($user))->toBeTrue();
});

test('view delegates to viewAny', function () {
    Permission::create(['name' => 'view posts']);
    $user = User::factory()->create();
    $user->givePermissionTo('view posts');

    $policy = new PostPolicy;

    expect($policy->view($user))->toBeTrue();
});

test('create checks the correct permission', function () {
    Permission::create(['name' => 'create posts']);
    $user = User::factory()->create();
    $user->givePermissionTo('create posts');

    $policy = new PostPolicy;

    expect($policy->create($user))->toBeTrue();
});

test('update checks the correct permission', function () {
    Permission::create(['name' => 'update posts']);
    $user = User::factory()->create();
    $user->givePermissionTo('update posts');

    $policy = new PostPolicy;

    expect($policy->update($user))->toBeTrue();
});

test('reorder delegates to update', function () {
    Permission::create(['name' => 'update posts']);
    $user = User::factory()->create();
    $user->givePermissionTo('update posts');

    $policy = new PostPolicy;

    expect($policy->reorder($user))->toBeTrue();
});

test('deleteAny checks the correct permission', function () {
    Permission::create(['name' => 'delete posts']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete posts');

    $policy = new PostPolicy;

    expect($policy->deleteAny($user))->toBeTrue();
});

test('delete delegates to deleteAny', function () {
    Permission::create(['name' => 'delete posts']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete posts');

    $policy = new PostPolicy;

    expect($policy->delete($user))->toBeTrue();
});

test('restore delegates to delete', function () {
    Permission::create(['name' => 'delete posts']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete posts');

    $policy = new PostPolicy;

    expect($policy->restore($user))->toBeTrue();
});

test('forceDelete delegates to delete', function () {
    Permission::create(['name' => 'delete posts']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete posts');

    $policy = new PostPolicy;

    expect($policy->forceDelete($user))->toBeTrue();
});

test('base policy is abstract', function () {
    $reflection = new ReflectionClass(BasePolicy::class);

    expect($reflection->isAbstract())->toBeTrue();
});
