<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Bootstrap pagination
        // https://laravel.com/framework/docs/pagination#using-bootstrap
        Paginator::useBootstrapFive();

        // Preventing lazy loading catches N+1 queries in admin tables and public pages early.
        Model::preventLazyLoading(! $this->app->isProduction());
    }
}
