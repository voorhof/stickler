<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Pages\Spatie\Settings\Terms;
use App\Filament\Traits\HasSettingsPageDefaults;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Settings\TermsSettings;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    Role::factory()->create(['name' => 'Restricted']);
    $adminRole = Role::factory()->create(['name' => 'Admin']);

    $accessAdmin = Permission::create(['name' => 'access admin']);
    $accessSettings = Permission::create(['name' => 'access settings']);
    $editSettings = Permission::create(['name' => 'edit settings']);

    $adminRole->givePermissionTo([$accessAdmin, $accessSettings, $editSettings]);

    $this->adminUser->assignRole($adminRole);
});

test('the page uses the HasSettingsPageDefaults trait', function () {
    expect(in_array(HasSettingsPageDefaults::class, class_uses_recursive(Terms::class), true))->toBeTrue();
});

it('has a getLogname method that returns terms_settings', function () {
    $page = new Terms;
    $method = new ReflectionMethod(Terms::class, 'getLogname');
    $method->setAccessible(true);

    expect($method->invoke($page))->toBe('terms_settings');
});

it('has a slug that returns terms-settings', function () {
    $reflection = new ReflectionClass(Terms::class);
    $property = $reflection->getProperty('slug');
    $property->setAccessible(true);

    expect($property->getValue())->toBe('terms-settings');
});

it('can load the terms settings page for users with access settings permission', function () {
    $this->actingAs($this->adminUser)
        ->get(Terms::getUrl())
        ->assertSuccessful()
        ->assertSeeLivewire(Terms::class);
});

it('denies access to the terms settings page for users without access settings permission', function () {
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole('Restricted');
    // Restricted needs access admin to reach the panel, give it minimally
    $restrictedUser->givePermissionTo(Permission::findByName('access admin'));

    $this->actingAs($restrictedUser)
        ->get(Terms::getUrl())
        ->assertForbidden();
});

it('pre-fills the form with current terms settings values', function () {
    $settings = app(TermsSettings::class);
    $settings->terms_and_conditions = '<h1>Terms and conditions</h1>';
    $settings->privacy_policy = '<h1>Privacy policy</h1>';
    $settings->cookie_policy = '<h1>Cookie policy</h1>';
    $settings->save();

    $this->actingAs($this->adminUser);

    livewire(Terms::class)
        ->assertSchemaStateSet([
            'terms_and_conditions' => '<h1>Terms and conditions</h1>',
            'privacy_policy' => '<h1>Privacy policy</h1>',
            'cookie_policy' => '<h1>Cookie policy</h1>',
        ]);
});

it('can save updated terms settings values', function () {
    $this->actingAs($this->adminUser);

    livewire(Terms::class)
        ->fillForm([
            'terms_and_conditions' => '<h1>Updated Terms and conditions</h1>',
            'privacy_policy' => '<h1>Updated Privacy policy</h1>',
            'cookie_policy' => '<h1>Updated Cookie policy</h1>',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $settings = app(TermsSettings::class)->refresh();

    expect($settings->terms_and_conditions)->toBe('<h1>Updated Terms and conditions</h1>')
        ->and($settings->privacy_policy)->toBe('<h1>Updated Privacy policy</h1>')
        ->and($settings->cookie_policy)->toBe('<h1>Updated Cookie policy</h1>');
});

it('validates required fields on the terms settings page', function () {
    $this->actingAs($this->adminUser);

    livewire(Terms::class)
        ->fillForm([
            'terms_and_conditions' => null,
            'privacy_policy' => null,
            'cookie_policy' => null,
        ])
        ->call('save')
        ->assertHasFormErrors();
});
