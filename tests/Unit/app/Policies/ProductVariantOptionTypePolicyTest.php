<?php

use App\Models\Permission;
use App\Models\User;
use App\Policies\ProductVariantOptionTypePolicy;

beforeEach(function () {
    $this->policy = new ProductVariantOptionTypePolicy;
});

test('product variant option type policy delegates all methods to base policy', function () {
    Permission::create(['name' => 'view products']);
    Permission::create(['name' => 'create products']);
    Permission::create(['name' => 'update products']);
    Permission::create(['name' => 'delete products']);

    $user = User::factory()->create();
    $user->givePermissionTo('view products', 'create products', 'update products', 'delete products');

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
