<?php

use App\Providers\HeadServiceProvider;
use Illuminate\Support\ServiceProvider;
use Laravel\Head\Facades\Head;

test('it extends ServiceProvider', function () {
    $provider = new HeadServiceProvider(app());

    expect($provider)->toBeInstanceOf(ServiceProvider::class);
});

test('it executes boot method without errors', function () {
    $provider = new HeadServiceProvider(app());

    expect(fn () => $provider->boot())->not->toThrow(Throwable::class);
});

test('it is registered in the application', function () {
    expect(app()->getLoadedProviders())->toHaveKey(HeadServiceProvider::class);
});

test('it configures head defaults on boot', function () {
    $provider = new HeadServiceProvider(app());
    $provider->boot();

    $html = Head::toHtml();

    expect($html)->toContain('<title>'.config('app.name').'</title>')
        ->and($html)->toContain(config('app.name').' website template')
        ->and($html)->toContain('name="viewport" content="width=device-width, initial-scale=1"')
        ->and($html)->toContain('name="robots" content="index, follow');
});
