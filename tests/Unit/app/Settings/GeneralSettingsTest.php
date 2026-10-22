<?php

use App\Settings\GeneralSettings;
use Illuminate\Database\Eloquent\Builder;
use Spatie\LaravelSettings\Settings;

describe('class structure', function () {
    test('it extends settings', function () {
        expect(new GeneralSettings)->toBeInstanceOf(Settings::class);
    });

    test('it has correct group', function () {
        expect(GeneralSettings::group())->toBe('general');
    });

    test('it has empty encrypted array', function () {
        expect(GeneralSettings::encrypted())->toBe([]);
    });
});

describe('values', function () {
    test('it can set and save all values', function () {
        $settings = app(GeneralSettings::class);
        $settings->contact_name = 'John Doe';
        $settings->contact_address = '123 Test St';
        $settings->contact_city = 'Test City';
        $settings->contact_country = 'Test Country';
        $settings->contact_company_name = 'Test Company';
        $settings->contact_company_number = '123456';
        $settings->contact_email = 'test@example.com';
        $settings->contact_phone = '+123456789';
        $settings->social_facebook = 'https://facebook.com/test';
        $settings->social_instagram = 'https://instagram.com/test';
        $settings->social_linkedin = 'https://linkedin.com/test';
        $settings->save();

        $fresh = app(GeneralSettings::class);
        expect($fresh->contact_name)->toBe('John Doe')
            ->and($fresh->contact_address)->toBe('123 Test St')
            ->and($fresh->contact_city)->toBe('Test City')
            ->and($fresh->contact_country)->toBe('Test Country')
            ->and($fresh->contact_company_name)->toBe('Test Company')
            ->and($fresh->contact_company_number)->toBe('123456')
            ->and($fresh->contact_email)->toBe('test@example.com')
            ->and($fresh->contact_phone)->toBe('+123456789')
            ->and($fresh->social_facebook)->toBe('https://facebook.com/test')
            ->and($fresh->social_instagram)->toBe('https://instagram.com/test')
            ->and($fresh->social_linkedin)->toBe('https://linkedin.com/test');
    });
});

describe('relationships', function () {
    test('it provides activities relationship builder', function () {
        $settings = new GeneralSettings;

        expect($settings->activities())->toBeInstanceOf(Builder::class);
    });
});
