<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PageViewPolicy extends BasePolicy
{
    /**
     * Resolve the multi-word 'page views' to single word permission name used by the seeder ("view pageviews").
     */
    protected function resourceName(): string
    {
        return 'pageviews';
    }

    /**
     * Determine whether the user can create page views.
     * No user can create page views, only the app system can.
     */
    public function create(?User $user = null): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the page view.
     * No user can update page views, only the app system can.
     */
    public function update(?User $user = null, ?Model $model = null): bool
    {
        return false;
    }
}
