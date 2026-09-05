<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Pages\Spatie\Settings\General;
use App\Filament\Traits\HasSettingsPageDefaults;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Settings\GeneralSettings;
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
    expect(in_array(HasSettingsPageDefaults::class, class_uses_recursive(General::class), true))->toBeTrue();
});

it('has a getLogname method that returns general_settings', function () {
    $page = new General;
    $method = new ReflectionMethod(General::class, 'getLogname');
    $method->setAccessible(true);

    expect($method->invoke($page))->toBe('general_settings');
});

it('has a slug that returns general-settings', function () {
    $reflection = new ReflectionClass(General::class);
    $property = $reflection->getProperty('slug');
    $property->setAccessible(true);

    expect($property->getValue())->toBe('general-settings');
});

it('can load the general settings page for users with access settings permission', function () {
    $this->actingAs($this->adminUser)
        ->get(General::getUrl())
        ->assertSuccessful()
        ->assertSeeLivewire(General::class);
});

it('denies access to the general settings page for users without access settings permission', function () {
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole('Restricted');
    // Restricted needs access admin to reach the panel, give it minimally
    $restrictedUser->givePermissionTo(Permission::findByName('access admin'));

    $this->actingAs($restrictedUser)
        ->get(General::getUrl())
        ->assertForbidden();
});

it('pre-fills the form with current general settings values', function () {
    $settings = app(GeneralSettings::class);
    $settings->contact_name = 'John Doe';
    $settings->contact_address = '123 Main St';
    $settings->contact_city = 'New York';
    $settings->contact_country = 'USA';
    $settings->contact_email = 'contact@example.com';
    $settings->contact_phone = '+1234567890';
    $settings->contact_company_name = 'Company Inc';
    $settings->contact_company_number = '0123456789';
    $settings->social_facebook = 'https://www.facebook.com/';
    $settings->social_instagram = 'https://www.instagram.com/';
    $settings->social_linkedin = 'https://www.linkedin.com/';
    $settings->save();

    $this->actingAs($this->adminUser);

    livewire(General::class)
        ->assertSchemaStateSet([
            'contact_name' => 'John Doe',
            'contact_address' => '123 Main St',
            'contact_city' => 'New York',
            'contact_country' => 'USA',
            'contact_email' => 'contact@example.com',
            'contact_phone' => '+1234567890',
            'contact_company_name' => 'Company Inc',
            'contact_company_number' => '0123456789',
            'social_facebook' => 'https://www.facebook.com/',
            'social_instagram' => 'https://www.instagram.com/',
            'social_linkedin' => 'https://www.linkedin.com/',
        ]);
});

it('can save updated general settings values', function () {
    $this->actingAs($this->adminUser);

    livewire(General::class)
        ->fillForm([
            'contact_name' => 'John Doe',
            'contact_address' => '123 Main St Update',
            'contact_city' => 'New York Update',
            'contact_country' => 'USA Update',
            'contact_email' => 'contact@example.com Update',
            'contact_phone' => '+1234567890 Update',
            'contact_company_name' => 'Company Inc Update',
            'contact_company_number' => '0123456789 Update',
            'social_facebook' => 'https://www.facebook.com/update',
            'social_instagram' => 'https://www.instagram.com/update',
            'social_linkedin' => 'https://www.linkedin.com/update',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $settings = app(GeneralSettings::class)->refresh();

    expect($settings->contact_name)->toBe('John Doe')
        ->and($settings->contact_address)->toBe('123 Main St Update')
        ->and($settings->contact_city)->toBe('New York Update')
        ->and($settings->contact_country)->toBe('USA Update')
        ->and($settings->contact_email)->toBe('contact@example.com Update')
        ->and($settings->contact_phone)->toBe('+1234567890 Update')
        ->and($settings->contact_company_name)->toBe('Company Inc Update')
        ->and($settings->contact_company_number)->toBe('0123456789 Update')
        ->and($settings->social_facebook)->toBe('https://www.facebook.com/update')
        ->and($settings->social_instagram)->toBe('https://www.instagram.com/update')
        ->and($settings->social_linkedin)->toBe('https://www.linkedin.com/update');
});

it('allows social media fields to be saved as null', function () {
    $this->actingAs($this->adminUser);

    livewire(General::class)
        ->fillForm([
            'contact_name' => 'John Doe',
            'contact_address' => '123 Main St',
            'contact_city' => 'New York',
            'contact_country' => 'USA',
            'contact_email' => 'contact@example.com',
            'contact_phone' => '+1234567890',
            'contact_company_name' => 'Company Inc',
            'contact_company_number' => 'BE123456789',
            'social_facebook' => null,
            'social_instagram' => null,
            'social_linkedin' => null,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = app(GeneralSettings::class)->refresh();

    expect($settings->social_instagram)->toBeNull()
        ->and($settings->social_linkedin)->toBeNull();
});

it('validates required fields on the general settings page', function () {
    $this->actingAs($this->adminUser);

    livewire(General::class)
        ->fillForm([
            'contact_name' => null,
            'contact_address' => null,
            'contact_city' => null,
            'contact_country' => null,
            'contact_email' => null,
            'contact_phone' => null,
            'contact_company_name' => null,
            'contact_company_number' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'contact_name' => 'required',
            'contact_address' => 'required',
            'contact_city' => 'required',
            'contact_country' => 'required',
            'contact_email' => 'required',
            'contact_phone' => 'required',
            'contact_company_name' => 'required',
            'contact_company_number' => 'required',
        ]);
});

it('validates max length on general settings fields', function () {
    $this->actingAs($this->adminUser);

    $tooLong = str_repeat('a', 251);

    livewire(General::class)
        ->fillForm([
            'contact_name' => $tooLong,
            'contact_address' => $tooLong,
            'contact_city' => $tooLong,
            'contact_country' => $tooLong,
            'contact_email' => $tooLong,
            'contact_phone' => $tooLong,
            'contact_company_name' => $tooLong,
            'contact_company_number' => $tooLong,
            'social_facebook' => $tooLong,
            'social_instagram' => $tooLong,
            'social_linkedin' => $tooLong,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'contact_name' => 'max',
            'contact_address' => 'max',
            'contact_city' => 'max',
            'contact_country' => 'max',
            'contact_email' => 'max',
            'contact_phone' => 'max',
            'contact_company_name' => 'max',
            'contact_company_number' => 'max',
            'social_facebook' => 'max',
            'social_instagram' => 'max',
            'social_linkedin' => 'max',
        ]);
});
