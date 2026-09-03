<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Filament\Pages\Dashboard;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    User::factory()->create();
    Role::factory()->create(['name' => 'No admin']);
    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $accessAdminPermission = Permission::create(['name' => 'access admin']);
    $adminRole->givePermissionTo($accessAdminPermission);
});

it('redirects guest users to the login page', function () {
    get('/admin')
        ->assertRedirect('/admin/login');
});

it('can load the login page', function () {
    get('/admin/login')
        ->assertSuccessful()
        ->assertSeeLivewire(Login::class);
});

it('can login through the admin panel login page', function () {
    $adminUser = User::factory()->create(['password' => 'password']);
    $adminUser->assignRole('Admin');

    livewire(Login::class)
        ->fillForm([
            'email' => $adminUser->email,
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertRedirect('/admin');

    assertAuthenticated();
});

it('can logout of the admin panel', function () {
    $adminUser = User::factory()->create();
    $adminUser->assignRole('Admin');

    actingAs($adminUser)
        ->post('/admin/logout')
        ->assertRedirect('/admin/login');

    assertGuest();
});

it('grants dashboard access to users with admin panel permission when authenticated', function () {
    $adminUser = User::factory()->create();
    $adminUser->assignRole('Admin');

    $this->actingAs($adminUser)
        ->get('/admin')
        ->assertSuccessful()
        ->assertSeeLivewire(Dashboard::class);
});

it('redirects unverified users to the email verification prompt', function () {
    $adminUser = User::factory()->unverified()->create();
    $adminUser->assignRole('Admin');

    $this->actingAs($adminUser)
        ->get('/admin')
        ->assertRedirect('/admin/email-verification/prompt');
});

it('denies dashboard access to users without admin panel permission', function () {
    $noAdminUser = User::factory()->create();
    $noAdminUser->assignRole('No admin');

    $this->actingAs($noAdminUser)
        ->get('/admin')
        ->assertForbidden();
});
