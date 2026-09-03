<?php

namespace App\Providers\Spatie;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AccessServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Role and Permission Gates
        |--------------------------------------------------------------------------
        |
        | Authorization settings and globally used role and permission gates.
        |
        */

        // Implicitly grant the "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can(), for example inside the policies
        Gate::before(function (User $user) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        // Grant access to the Log Viewer in production environment when the user has the "access logs" permission
        // https://log-viewer.opcodes.io/docs/3.x/configuration/access-to-log-viewer#via-viewlogviewer-gate
        Gate::define('viewLogViewer', function (User $user) {
            return $user->can('access logs');
        });

        Gate::define('downloadLogFile', function (User $user) {
            return $user->can('download logs');
        });

        Gate::define('deleteLogFile', function (User $user) {
            return $user->can('delete logs');
        });
    }
}
