<?php

use App\Settings\TermsSettings;
use Illuminate\Database\Eloquent\Builder;
use Spatie\LaravelSettings\Settings;

describe('class structure', function () {
    test('it extends settings', function () {
        expect(new TermsSettings)->toBeInstanceOf(Settings::class);
    });

    test('it has correct group', function () {
        expect(TermsSettings::group())->toBe('terms');
    });
});

describe('values', function () {
    test('it can set and save all values', function () {
        $settings = app(TermsSettings::class);
        $settings->terms_and_conditions = '<p>Custom Terms</p>';
        $settings->privacy_policy = '<p>Custom Privacy</p>';
        $settings->cookie_policy = '<p>Custom Cookie</p>';
        $settings->save();

        $fresh = app(TermsSettings::class);
        expect($fresh->terms_and_conditions)->toBe('<p>Custom Terms</p>')
            ->and($fresh->privacy_policy)->toBe('<p>Custom Privacy</p>')
            ->and($fresh->cookie_policy)->toBe('<p>Custom Cookie</p>');
    });
});

describe('relationships', function () {
    test('it provides activities relationship builder', function () {
        $settings = new TermsSettings;

        expect($settings->activities())->toBeInstanceOf(Builder::class);
    });
});
