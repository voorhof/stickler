<?php

namespace App\Providers;

use App\Services\CartService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\TelescopeServiceProvider as LaravelTelescopeServiceProvider;

/*
|--------------------------------------------------------------------------
| Service Registration
|--------------------------------------------------------------------------
*/

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind CartService as a singleton so the internal cart cache is shared across all view composers
        $this->app->singleton(CartService::class);

        if ($this->app->isProduction()) {
            return;
        }

        // LaravelTelescopeServiceProvider bootstraps TelescopeServiceProvider
        foreach ([LaravelTelescopeServiceProvider::class, TelescopeServiceProvider::class] as $provider) {
            if (class_exists($provider)) {
                $this->app->register($provider);
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Bootstrap pagination
        // https://laravel.com/framework/docs/pagination#using-bootstrap
        Paginator::useBootstrapFive();

        // Strict mode catches lazy loading, discarded attributes, and missing attributes early.
        Model::shouldBeStrict(! $this->app->isProduction());

        // Share cart item count with all views
        View::composer('*', function ($view) {
            $cart = app(CartService::class)->findCurrentCart(auth()->user());

            // Use a simple SUM query instead of loading the full items relationship
            $view->with('cartItemCount', $cart ? (int) $cart->cartItems()->sum('quantity') : 0);
        });
    }
}
