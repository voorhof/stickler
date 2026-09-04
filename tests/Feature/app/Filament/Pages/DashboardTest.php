<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Filament\Pages\Dashboard;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create([
        'password' => 'password',
    ]);

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo(Permission::create(['name' => 'access admin']));

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('can load the dashboard page', function () {
    get('/admin')
        ->assertSuccessful()
        ->assertSeeLivewire(Dashboard::class);
});

it('has correct columns configuration', function () {
    $dashboard = new Dashboard;

    expect($dashboard->getColumns())->toBe([
        'md' => 6,
        '2xl' => 6,
    ]);
});

it('can render dashboard livewire component', function () {
    livewire(Dashboard::class)
        ->assertSuccessful();
});
