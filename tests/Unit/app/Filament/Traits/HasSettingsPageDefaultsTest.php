<?php

use App\Filament\Pages\Spatie\Settings\General;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Settings\GeneralSettings;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()->setLocale('en_US');
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    $this->adminRole = Role::factory()->create(['name' => 'Admin', 'guard_name' => 'web']);
    $accessAdmin = Permission::create(['name' => 'access admin', 'guard_name' => 'web']);
    $accessSettings = Permission::create(['name' => 'access settings', 'guard_name' => 'web']);
    $editSettings = Permission::create(['name' => 'edit settings', 'guard_name' => 'web']);

    $this->adminRole->givePermissionTo([$accessAdmin, $accessSettings, $editSettings]);

    $this->adminUser->assignRole($this->adminRole);
});

it('returns correct navigation group', function () {
    expect(General::getNavigationGroup())->toBe(__('Settings'));
});

it('allows access for users with access settings permission', function () {
    $this->actingAs($this->adminUser);

    expect(General::canAccess())->toBeTrue();
});

it('denies access for users without access settings permission', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    expect(General::canAccess())->toBeFalse();
});

it('allows edit for users with edit settings permission', function () {
    $this->actingAs($this->adminUser);
    $page = new General;

    expect($page->canEdit())->toBeTrue();
});

it('denies edit for users without edit settings permission', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::factory()->create(['name' => 'Viewer', 'guard_name' => 'web']));
    $this->actingAs($user);
    $page = new General;

    expect($page->canEdit())->toBeFalse();
});

it('returns correct saved notification title', function () {
    $page = new General;

    expect($page->getSavedNotificationTitle())->toBe(__('Ok! Settings have been saved.'));
});

it('saves settings when saving via settings page', function () {
    $this->actingAs($this->adminUser);

    livewire(General::class)
        ->fillForm([
            'contact_address' => 'Test Address',
            'contact_city' => 'Test City',
            'contact_country' => 'Test Country',
            'contact_email' => 'test@example.com',
            'contact_phone' => '123456789',
            'contact_company_name' => 'Test Company',
            'contact_company_number' => 'BE999999999',
            'social_linkedin' => 'https://www.linkedin.com/',
            'social_instagram' => 'https://www.instagram.com/',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $settings = app(GeneralSettings::class);
    expect($settings->contact_address)->toBe('Test Address');
});
