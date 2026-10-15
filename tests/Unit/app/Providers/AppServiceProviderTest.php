<?php

use App\Providers\AppServiceProvider;
use Illuminate\Support\ServiceProvider;

test('it extends ServiceProvider', function () {
    $provider = new AppServiceProvider(app());

    expect($provider)->toBeInstanceOf(ServiceProvider::class);
});

test('it executes register method without errors', function () {
    $provider = new AppServiceProvider(app());

    expect(fn () => $provider->register())->not->toThrow(Throwable::class);
});

test('it executes boot method without errors', function () {
    $provider = new AppServiceProvider(app());

    expect(fn () => $provider->boot())->not->toThrow(Throwable::class);
});

test('it is registered in the application', function () {
    expect(app()->getLoadedProviders())->toHaveKey(AppServiceProvider::class);
});
