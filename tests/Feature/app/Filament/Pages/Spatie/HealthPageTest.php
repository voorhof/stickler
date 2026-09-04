<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Filament\Pages\Spatie\Health;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    Role::factory()->create(['name' => 'Restricted']);
    $adminRole = Role::factory()->create(['name' => 'Admin']);

    $accessAdmin = Permission::create(['name' => 'access admin']);
    $accessHealthPage = Permission::create(['name' => 'access health page']);

    $adminRole->givePermissionTo([$accessAdmin, $accessHealthPage]);

    $this->adminUser->assignRole($adminRole);
});

it('can load the health page for users with access health page permission', function () {
    $this->actingAs($this->adminUser)
        ->get(Health::getUrl())
        ->assertSuccessful()
        ->assertSeeLivewire(Health::class)
        ->assertSee(__('Website health status'));
});

it('denies access to the health page for users without access health page permission', function () {
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole('Restricted');
    $restrictedUser->givePermissionTo(Permission::findByName('access admin'));

    $this->actingAs($restrictedUser)
        ->get(Health::getUrl())
        ->assertForbidden();
});

it('has the correct health page title', function () {
    $this->actingAs($this->adminUser);

    expect(new Health()->getTitle())->toBe(__('Website health status'));
});

it('has the correct health page navigation label', function () {
    expect(Health::getNavigationLabel())->toBe(__('Website health status'));
});

it('has the correct health page navigation group', function () {
    expect(Health::getNavigationGroup())->toBe(__('Settings'));
});

it('allows access when the user has the access health page permission', function () {
    $this->actingAs($this->adminUser);

    expect(Health::canAccess())->toBeTrue();
});

it('does not allow access when the user does not have the access health page permission', function () {
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole('Restricted');

    $this->actingAs($restrictedUser);

    expect(Health::canAccess())->toBeFalse();
});

it('has the health check header action', function () {
    $this->actingAs($this->adminUser);

    livewire(Health::class)
        ->assertActionExists('healthCheck');
});

it('can run the health check action from the page', function () {
    $this->actingAs($this->adminUser);

    Artisan::shouldReceive('call')
        ->with('health:queue-check-heartbeat')
        ->once();
    Artisan::shouldReceive('call')
        ->with('health:check')
        ->once();

    Artisan::shouldReceive('output')
        ->once()
        ->andReturn("Line 1\nResult: Success");

    livewire(Health::class)
        ->assertActionExists('healthCheck')
        ->callAction('healthCheck')
        ->assertNotified();
});

it('has the optimize header action', function () {
    $this->actingAs($this->adminUser);

    livewire(Health::class)
        ->assertActionExists('optimize');
});

it('can run the optimize action from the page', function () {
    $this->actingAs($this->adminUser);

    Artisan::shouldReceive('call')
        ->with('optimize')
        ->once();

    livewire(Health::class)
        ->assertActionExists('optimize')
        ->callAction('optimize')
        ->assertNotified();
});

it('has the optimizeClear header action', function () {
    $this->actingAs($this->adminUser);

    livewire(Health::class)
        ->assertActionExists('optimizeClear');
});

it('can run the optimizeClear action from the page', function () {
    $this->actingAs($this->adminUser);

    Artisan::shouldReceive('call')
        ->with('optimize:clear')
        ->once();

    livewire(Health::class)
        ->assertActionExists('optimizeClear')
        ->callAction('optimizeClear')
        ->assertNotified();
});
