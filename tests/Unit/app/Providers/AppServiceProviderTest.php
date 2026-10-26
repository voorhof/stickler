<?php

use App\Providers\AppServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

test('it extends service provider', function () {
    expect(new AppServiceProvider(app()))->toBeInstanceOf(ServiceProvider::class);
});

test('it is registered in the application', function () {
    expect(app()->getLoadedProviders())->toHaveKey(AppServiceProvider::class);
});

test('it uses bootstrap five pagination', function () {
    expect(Paginator::$defaultView)->toBe('pagination::bootstrap-5')
        ->and(Paginator::$defaultSimpleView)->toBe('pagination::simple-bootstrap-5');
});

test('it enables strict Eloquent behavior outside production', function () {
    expect(Model::preventsLazyLoading())->toBeTrue()
        ->and(Model::preventsSilentlyDiscardingAttributes())->toBeTrue()
        ->and(Model::preventsAccessingMissingAttributes())->toBeTrue();
});
