<?php

namespace App\Settings;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Builder;
use Spatie\LaravelSettings\Settings;

class TermsSettings extends Settings
{
    /** Define your settings here */
    public string $terms_and_conditions = '<h1>Algemene voorwaarden</h1>';

    public string $privacy_policy = '<h1>Privacy policy</h1>';

    public string $cookie_policy = '<h1>Cookie policy</h1>';

    /** The group the settings belong to */
    public static function group(): string
    {
        return 'terms';
    }

    /*
    |--------------------------------------------------------------------------
    | Eloquent Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the activities associated with services settings.
     *
     * @return Builder<Activity>
     */
    public function activities(): Builder
    {
        return Activity::inLog('terms_settings');
    }
}
