<?php

use App\Filament\Filters\BooleanFilter;
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
        'view tasks',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('has no default name', function () {
    $filter = BooleanFilter::make('test');

    expect($filter->getName())->toBe('test');
});

it('can set the operator via fluent method', function () {
    $filter = BooleanFilter::make('test')
        ->operator(false);

    expect($filter)->toBeInstanceOf(BooleanFilter::class);
});
