<?php

use App\Models\Permission;
use App\Models\User;
use App\Policies\ActivityPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new ActivityPolicy;
});

test('viewAny allows users with view activities permission', function () {
    Permission::create(['name' => 'view activities']);
    $user = User::factory()->create();
    $user->givePermissionTo('view activities');

    expect($this->policy->viewAny($user))->toBeTrue();
});

test('viewAny forbids users without view activities permission', function () {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse();
});

test('view delegates to viewAny and allows users with view activities permission', function () {
    Permission::create(['name' => 'view activities']);
    $user = User::factory()->create();
    $user->givePermissionTo('view activities');

    expect($this->policy->view($user))->toBeTrue();
});

test('view delegates to viewAny and forbids users without view activities permission', function () {
    $user = User::factory()->create();

    expect($this->policy->view($user))->toBeFalse();
});

test('create always returns false', function () {
    expect($this->policy->create())->toBeFalse();
});

test('update always returns false', function () {
    expect($this->policy->update())->toBeFalse();
});

test('deleteAny allows users with delete activities permission', function () {
    Permission::create(['name' => 'delete activities']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete activities');

    expect($this->policy->deleteAny($user))->toBeTrue();
});

test('deleteAny forbids users without delete activities permission', function () {
    $user = User::factory()->create();

    expect($this->policy->deleteAny($user))->toBeFalse();
});

test('delete delegates to deleteAny and allows users with delete activities permission', function () {
    Permission::create(['name' => 'delete activities']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete activities');

    expect($this->policy->delete($user))->toBeTrue();
});

test('delete delegates to deleteAny and forbids users without delete activities permission', function () {
    $user = User::factory()->create();

    expect($this->policy->delete($user))->toBeFalse();
});

test('forceDeleteAny delegates to deleteAny and allows users with delete activities permission', function () {
    Permission::create(['name' => 'delete activities']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete activities');

    expect($this->policy->forceDeleteAny($user))->toBeTrue();
});

test('forceDeleteAny delegates to deleteAny and forbids users without delete activities permission', function () {
    $user = User::factory()->create();

    expect($this->policy->forceDeleteAny($user))->toBeFalse();
});

test('forceDelete delegates to delete and allows users with delete activities permission', function () {
    Permission::create(['name' => 'delete activities']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete activities');

    expect($this->policy->forceDelete($user))->toBeTrue();
});

test('forceDelete delegates to delete and forbids users without delete activities permission', function () {
    $user = User::factory()->create();

    expect($this->policy->forceDelete($user))->toBeFalse();
});
