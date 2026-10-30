<?php

use App\Models\Permission;
use App\Models\User;
use App\Policies\CartItemPolicy;

beforeEach(function () {
    $this->policy = new CartItemPolicy;
});

test('cart item policy delegates all methods to base policy', function () {
    Permission::create(['name' => 'view carts']);
    Permission::create(['name' => 'create carts']);
    Permission::create(['name' => 'update carts']);
    Permission::create(['name' => 'delete carts']);

    $user = User::factory()->create();
    $user->givePermissionTo('view carts', 'create carts', 'update carts', 'delete carts');

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
