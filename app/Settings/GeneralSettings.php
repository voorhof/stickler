<?php

namespace App\Settings;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Builder;
use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    /** Define your settings here */
    public string $contact_name = '';

    public string $contact_address = '';

    public string $contact_city = '';

    public string $contact_country = '';

    public string $contact_company_name = '';

    public string $contact_company_number = '';

    public string $contact_email = '';

    public string $contact_phone = '';

    public ?string $social_facebook = '';

    public ?string $social_instagram = '';

    public ?string $social_linkedin = '';

    /** The group the settings belong to */
    public static function group(): string
    {
        return 'general';
    }

    /** The encrypted settings */
    public static function encrypted(): array
    {
        return [
            //
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Eloquent Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the activities associated with general settings.
     *
     * @return Builder<Activity>
     */
    public function activities(): Builder
    {
        return Activity::inLog('general_settings');
    }
}
