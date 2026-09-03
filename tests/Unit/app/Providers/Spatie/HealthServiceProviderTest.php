<?php

use App\Providers\Spatie\HealthServiceProvider;
use Illuminate\Support\ServiceProvider;
use Spatie\Health\Facades\Health;

beforeEach(function () {
    Health::clearChecks();
});

test('it extends ServiceProvider', function () {
    $provider = new HealthServiceProvider(app());

    expect($provider)->toBeInstanceOf(ServiceProvider::class);
});

test('it executes boot method without errors', function () {
    $provider = new HealthServiceProvider(app());

    $provider->boot();

    expect(true)->toBeTrue();
});

test('it is registered in the application', function () {
    expect(app()->getLoadedProviders())->toHaveKey(HealthServiceProvider::class);
});

test('it registers health checks on boot', function () {
    $provider = new HealthServiceProvider(app());
    $provider->boot();

    $checks = Health::registeredChecks();

    expect($checks)->not->toBeEmpty();
});
