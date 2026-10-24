<?php

namespace App\Providers\Spatie;

use App\Observers\Spatie\BusinessHoursObserver;
use Illuminate\Support\ServiceProvider;
use LaBoiteACode\FilamentBusinessHours\Models\BusinessHours;

class BusinessHoursServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        BusinessHours::observe(BusinessHoursObserver::class);
    }
}
