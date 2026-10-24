<?php

use App\Providers\HeadServiceProvider;
use Illuminate\Support\ServiceProvider;
use Laravel\Head\Facades\Head;

test('it extends service provider', function () {
    expect(new HeadServiceProvider(app()))->toBeInstanceOf(ServiceProvider::class);
});

test('it is registered in the application', function () {
    expect(app()->getLoadedProviders())->toHaveKey(HeadServiceProvider::class);
});

test('it configures head title and description', function () {
    new HeadServiceProvider(app())->boot();

    $html = Head::toHtml();

    expect($html)->toContain('<title>'.config('app.name').'</title>')
        ->and($html)->toContain(config('app.name').' website template');
});

test('it configures viewport and robots meta tags', function () {
    new HeadServiceProvider(app())->boot();

    $html = Head::toHtml();

    expect($html)->toContain('name="viewport" content="width=device-width, initial-scale=1"')
        ->and($html)->toContain('name="robots" content="index, follow')
        ->and($html)->toContain('max-image-preview:large');
});

test('it configures og tags', function () {
    new HeadServiceProvider(app())->boot();

    $html = Head::toHtml();

    expect($html)->toContain('property="og:type" content="website"')
        ->and($html)->toContain('property="og:site_name"')
        ->and($html)->toContain('property="og:locale"')
        ->and($html)->toContain('property="og:image"');
});

test('it configures pwa and apple web app settings', function () {
    new HeadServiceProvider(app())->boot();

    $html = Head::toHtml();

    expect($html)->toContain('name="apple-mobile-web-app-title"')
        ->and($html)->toContain('apple-touch-icon')
        ->and($html)->toContain('site.webmanifest');
});

test('it configures favicons', function () {
    new HeadServiceProvider(app())->boot();

    $html = Head::toHtml();

    expect($html)->toContain('favicon.ico')
        ->and($html)->toContain('favicon-16x16.png')
        ->and($html)->toContain('favicon-32x32.png')
        ->and($html)->toContain('favicon.svg');
});

test('it configures error page head defaults', function () {
    new HeadServiceProvider(app())->boot();

    $html = Head::toHtml(404);

    expect($html)->toContain('noindex, follow');
});

test('it configures sitemap link', function () {
    new HeadServiceProvider(app())->boot();

    $html = Head::toHtml();

    expect($html)->toContain('sitemap.xml');
});
