<?php

use App\Providers\AppServiceProvider;
use Illuminate\Support\ServiceProvider;

test('it extends service provider', function () {
    expect(new AppServiceProvider(app()))->toBeInstanceOf(ServiceProvider::class);
});

test('it is registered in the application', function () {
    expect(app()->getLoadedProviders())->toHaveKey(AppServiceProvider::class);
});
