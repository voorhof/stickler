<?php

use App\Settings\GeneralSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\LaravelSettings\Settings;

uses(RefreshDatabase::class);

test('general settings extends settings', function () {
    $settings = new GeneralSettings;

    expect($settings)->toBeInstanceOf(Settings::class);
});

test('general settings has correct group', function () {
    expect(GeneralSettings::group())->toBe('general');
});

test('general settings has empty encrypted array', function () {
    expect(GeneralSettings::encrypted())->toBe([]);
});

test('general settings can set and save values', function () {
    $settings = app(GeneralSettings::class);
    $settings->contact_name = 'John Doe';
    $settings->contact_address = '123 Test St';
    $settings->contact_city = 'Test City';
    $settings->contact_country = 'Test Country';
    $settings->contact_company_name = 'Test Company';
    $settings->contact_company_number = '123456';
    $settings->contact_email = 'test@example.com';
    $settings->contact_phone = '+123456789';
    $settings->social_linkedin = 'https://linkedin.com/test';
    $settings->social_instagram = 'https://instagram.com/test';
    $settings->save();

    $fresh = app(GeneralSettings::class);
    expect($fresh->contact_address)->toBe('123 Test St')
        ->and($fresh->contact_name)->toBe('John Doe')
        ->and($fresh->contact_city)->toBe('Test City')
        ->and($fresh->contact_country)->toBe('Test Country')
        ->and($fresh->contact_company_name)->toBe('Test Company')
        ->and($fresh->contact_company_number)->toBe('123456')
        ->and($fresh->contact_email)->toBe('test@example.com')
        ->and($fresh->contact_phone)->toBe('+123456789')
        ->and($fresh->social_linkedin)->toBe('https://linkedin.com/test')
        ->and($fresh->social_instagram)->toBe('https://instagram.com/test');
});

test('general settings provides activities relationship builder', function () {
    $settings = new GeneralSettings;
    $builder = $settings->activities();

    expect($builder)->toBeInstanceOf(Builder::class);
});
