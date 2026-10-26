<?php

use App\Providers\AppServiceProvider;
use App\Providers\TelescopeServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\TelescopeServiceProvider as LaravelTelescopeServiceProvider;

test('it extends service provider', function () {
    expect(new AppServiceProvider(app()))->toBeInstanceOf(ServiceProvider::class);
});

test('it is registered in the application', function () {
    expect(app()->getLoadedProviders())->toHaveKey(AppServiceProvider::class);
});

test('it registers Telescope outside production', function () {
    $application = Mockery::mock(Application::class);
    $application->shouldReceive('isProduction')->once()->andReturnFalse();
    $application->shouldReceive('register')->once()->with(LaravelTelescopeServiceProvider::class);
    $application->shouldReceive('register')->once()->with(TelescopeServiceProvider::class);

    (new AppServiceProvider($application))->register();
});

test('it does not register Telescope in production', function () {
    $application = Mockery::mock(Application::class);
    $application->shouldReceive('isProduction')->once()->andReturnTrue();
    $application->shouldNotReceive('register');

    (new AppServiceProvider($application))->register();
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
