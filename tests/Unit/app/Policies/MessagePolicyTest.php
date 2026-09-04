<?php

use App\Models\Permission;
use App\Models\User;
use App\Policies\MessagePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new MessagePolicy;
});

test('viewAny allows users with view messages permission', function () {
    Permission::create(['name' => 'view messages']);
    $user = User::factory()->create();
    $user->givePermissionTo('view messages');

    expect($this->policy->viewAny($user))->toBeTrue();
});

test('viewAny forbids users without view messages permission', function () {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse();
});

test('view allows users with view messages permission', function () {
    Permission::create(['name' => 'view messages']);
    $user = User::factory()->create();
    $user->givePermissionTo('view messages');

    expect($this->policy->view($user))->toBeTrue();
});

test('view forbids users without view messages permission', function () {
    $user = User::factory()->create();

    expect($this->policy->view($user))->toBeFalse();
});

test('create allows all users to create messages', function () {
    expect($this->policy->create())->toBeTrue();
});

test('update allows users with update messages permission', function () {
    Permission::create(['name' => 'update messages']);
    $user = User::factory()->create();
    $user->givePermissionTo('update messages');

    expect($this->policy->update($user))->toBeTrue();
});

test('update forbids users without update messages permission', function () {
    $user = User::factory()->create();

    expect($this->policy->update($user))->toBeFalse();
});

test('reorder allows users with update messages permission', function () {
    Permission::create(['name' => 'update messages']);
    $user = User::factory()->create();
    $user->givePermissionTo('update messages');

    expect($this->policy->reorder($user))->toBeTrue();
});

test('reorder forbids users without update messages permission', function () {
    $user = User::factory()->create();

    expect($this->policy->reorder($user))->toBeFalse();
});

test('deleteAny allows users with delete messages permission', function () {
    Permission::create(['name' => 'delete messages']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete messages');

    expect($this->policy->deleteAny($user))->toBeTrue();
});

test('deleteAny forbids users without delete messages permission', function () {
    $user = User::factory()->create();

    expect($this->policy->deleteAny($user))->toBeFalse();
});

test('delete delegates to deleteAny and allows users with delete messages permission', function () {
    Permission::create(['name' => 'delete messages']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete messages');

    expect($this->policy->delete($user))->toBeTrue();
});

test('delete delegates to deleteAny and forbids users without delete messages permission', function () {
    $user = User::factory()->create();

    expect($this->policy->delete($user))->toBeFalse();
});

test('restoreAny delegates to deleteAny and allows users with delete messages permission', function () {
    Permission::create(['name' => 'delete messages']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete messages');

    expect($this->policy->restoreAny($user))->toBeTrue();
});

test('restoreAny forbids users without delete messages permission', function () {
    $user = User::factory()->create();

    expect($this->policy->restoreAny($user))->toBeFalse();
});

test('restore delegates to delete and allows users with delete messages permission', function () {
    Permission::create(['name' => 'delete messages']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete messages');

    expect($this->policy->restore($user))->toBeTrue();
});

test('restore forbids users without delete messages permission', function () {
    $user = User::factory()->create();

    expect($this->policy->restore($user))->toBeFalse();
});

test('forceDeleteAny delegates to deleteAny and allows users with delete messages permission', function () {
    Permission::create(['name' => 'delete messages']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete messages');

    expect($this->policy->forceDeleteAny($user))->toBeTrue();
});

test('forceDeleteAny forbids users without delete messages permission', function () {
    $user = User::factory()->create();

    expect($this->policy->forceDeleteAny($user))->toBeFalse();
});

test('forceDelete delegates to delete and allows users with delete messages permission', function () {
    Permission::create(['name' => 'delete messages']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete messages');

    expect($this->policy->forceDelete($user))->toBeTrue();
});

test('forceDelete forbids users without delete messages permission', function () {
    $user = User::factory()->create();

    expect($this->policy->forceDelete($user))->toBeFalse();
});
