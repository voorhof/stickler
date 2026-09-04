<?php

use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Projects\Pages\ListProject;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Filament\Resources\Tags\Pages\ListTags;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'create posts',
        'view posts',
        'create projects',
        'view projects',
        'create roles',
        'view roles',
        'create tags',
        'view tags',
        'create users',
        'view users',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('has create action on list posts page', function () {
    livewire(ListPosts::class)
        ->assertActionExists('create');
});

it('has create action on list projects page', function () {
    livewire(ListProject::class)
        ->assertActionExists('create');
});

it('has create action on list roles page', function () {
    livewire(ListRoles::class)
        ->assertActionExists('create');
});

it('has create action on list tags page', function () {
    livewire(ListTags::class)
        ->assertActionExists('create');
});

it('has create action on list users page', function () {
    livewire(ListUsers::class)
        ->assertActionExists('create');
});
