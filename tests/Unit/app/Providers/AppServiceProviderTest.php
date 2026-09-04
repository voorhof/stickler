<?php

use App\Providers\AppServiceProvider;
use Illuminate\Support\ServiceProvider;

test('it extends ServiceProvider', function () {
    $provider = new AppServiceProvider(app());

    expect($provider)->toBeInstanceOf(ServiceProvider::class);
});

test('it executes register method without errors', function () {
    $provider = new AppServiceProvider(app());

    $provider->register();

    expect(true)->toBeTrue();
});

test('it executes boot method without errors', function () {
    $provider = new AppServiceProvider(app());

    $provider->boot();

    expect(true)->toBeTrue();
});

test('it is registered in the application', function () {
    expect(app()->getLoadedProviders())->toHaveKey(AppServiceProvider::class);
});
