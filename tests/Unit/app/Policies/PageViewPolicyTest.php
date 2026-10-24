<?php

use App\Models\Permission;
use App\Models\User;
use App\Policies\PageViewPolicy;

beforeEach(function () {
    $this->policy = new PageViewPolicy;
});

test('create always returns false', function () {
    expect($this->policy->create())->toBeFalse();
});

test('update always returns false', function () {
    expect($this->policy->update())->toBeFalse();
});

test('page view policy resolves the multi-word view permission', function () {
    Permission::create(['name' => 'view pageviews']);

    $user = User::factory()->create();
    $user->givePermissionTo('view pageviews');

    expect($this->policy->viewAny($user))->toBeTrue()
        ->and($this->policy->view($user))->toBeTrue();
});

test('page view policy denies viewing without the view pageviews permission', function () {
    Permission::create(['name' => 'view pageviews']);

    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse()
        ->and($this->policy->view($user))->toBeFalse();
});
