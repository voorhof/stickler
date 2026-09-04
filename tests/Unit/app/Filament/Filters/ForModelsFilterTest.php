<?php

use App\Filament\Filters\ForModelsFilter;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'view brands',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('has no default name', function () {
    $filter = ForModelsFilter::make('for_recipes');

    expect($filter->getName())->toBe('for_recipes');
});

it('can set a model name label', function () {
    $filter = ForModelsFilter::make('for_recipes')
        ->modelName(__('Recipes'));

    expect($filter->getLabel())->toBe(__('For :model', ['model' => __('Recipes')]));
});
