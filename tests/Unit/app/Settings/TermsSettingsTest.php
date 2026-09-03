<?php

use App\Settings\TermsSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\LaravelSettings\Settings;

uses(RefreshDatabase::class);

test('terms settings extends settings', function () {
    $settings = new TermsSettings;

    expect($settings)->toBeInstanceOf(Settings::class);
});

test('terms settings has correct group', function () {
    expect(TermsSettings::group())->toBe('terms');
});

test('terms settings can set and save values', function () {
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

test('terms settings provides activities relationship builder', function () {
    $settings = new TermsSettings;
    $builder = $settings->activities();

    expect($builder)->toBeInstanceOf(Builder::class);
});
