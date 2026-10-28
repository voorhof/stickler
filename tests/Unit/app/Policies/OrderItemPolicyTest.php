<?php

use App\Models\Permission;
use App\Models\User;
use App\Policies\OrderItemPolicy;

beforeEach(function () {
    $this->policy = new OrderItemPolicy;
});

test('order item policy delegates all methods to base policy', function () {
    Permission::create(['name' => 'view orders']);
    Permission::create(['name' => 'create orders']);
    Permission::create(['name' => 'update orders']);
    Permission::create(['name' => 'delete orders']);

    $user = User::factory()->create();
    $user->givePermissionTo('view orders', 'create orders', 'update orders', 'delete orders');

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
