<?php

use App\Models\Permission;
use App\Models\User;
use App\Policies\TagPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new TagPolicy;
});

test('viewAny allows users with view tags permission', function () {
    Permission::create(['name' => 'view tags']);
    $user = User::factory()->create();
    $user->givePermissionTo('view tags');

    expect($this->policy->viewAny($user))->toBeTrue();
});

test('viewAny forbids users without view tags permission', function () {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse();
});

test('view allows users with view tags permission', function () {
    Permission::create(['name' => 'view tags']);
    $user = User::factory()->create();
    $user->givePermissionTo('view tags');

    expect($this->policy->view($user))->toBeTrue();
});

test('view forbids users without view tags permission', function () {
    $user = User::factory()->create();

    expect($this->policy->view($user))->toBeFalse();
});

test('create allows users with create tags permission', function () {
    Permission::create(['name' => 'create tags']);
    $user = User::factory()->create();
    $user->givePermissionTo('create tags');

    expect($this->policy->create($user))->toBeTrue();
});

test('create forbids users without create tags permission', function () {
    $user = User::factory()->create();

    expect($this->policy->create($user))->toBeFalse();
});

test('update allows users with update tags permission', function () {
    Permission::create(['name' => 'update tags']);
    $user = User::factory()->create();
    $user->givePermissionTo('update tags');

    expect($this->policy->update($user))->toBeTrue();
});

test('update forbids users without update tags permission', function () {
    $user = User::factory()->create();

    expect($this->policy->update($user))->toBeFalse();
});

test('reorder allows users with update tags permission', function () {
    Permission::create(['name' => 'update tags']);
    $user = User::factory()->create();
    $user->givePermissionTo('update tags');

    expect($this->policy->reorder($user))->toBeTrue();
});

test('reorder forbids users without update tags permission', function () {
    $user = User::factory()->create();

    expect($this->policy->reorder($user))->toBeFalse();
});

test('deleteAny allows users with delete tags permission', function () {
    Permission::create(['name' => 'delete tags']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete tags');

    expect($this->policy->deleteAny($user))->toBeTrue();
});

test('deleteAny forbids users without delete tags permission', function () {
    $user = User::factory()->create();

    expect($this->policy->deleteAny($user))->toBeFalse();
});

test('delete delegates to deleteAny and allows users with delete tags permission', function () {
    Permission::create(['name' => 'delete tags']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete tags');

    expect($this->policy->delete($user))->toBeTrue();
});

test('delete delegates to deleteAny and forbids users without delete tags permission', function () {
    $user = User::factory()->create();

    expect($this->policy->delete($user))->toBeFalse();
});

test('restoreAny delegates to deleteAny and allows users with delete tags permission', function () {
    Permission::create(['name' => 'delete tags']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete tags');

    expect($this->policy->restoreAny($user))->toBeTrue();
});

test('restoreAny forbids users without delete tags permission', function () {
    $user = User::factory()->create();

    expect($this->policy->restoreAny($user))->toBeFalse();
});

test('restore delegates to delete and allows users with delete tags permission', function () {
    Permission::create(['name' => 'delete tags']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete tags');

    expect($this->policy->restore($user))->toBeTrue();
});

test('restore forbids users without delete tags permission', function () {
    $user = User::factory()->create();

    expect($this->policy->restore($user))->toBeFalse();
});

test('forceDeleteAny delegates to deleteAny and allows users with delete tags permission', function () {
    Permission::create(['name' => 'delete tags']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete tags');

    expect($this->policy->forceDeleteAny($user))->toBeTrue();
});

test('forceDeleteAny forbids users without delete tags permission', function () {
    $user = User::factory()->create();

    expect($this->policy->forceDeleteAny($user))->toBeFalse();
});

test('forceDelete delegates to delete and allows users with delete tags permission', function () {
    Permission::create(['name' => 'delete tags']);
    $user = User::factory()->create();
    $user->givePermissionTo('delete tags');

    expect($this->policy->forceDelete($user))->toBeTrue();
});

test('forceDelete forbids users without delete tags permission', function () {
    $user = User::factory()->create();

    expect($this->policy->forceDelete($user))->toBeFalse();
});
