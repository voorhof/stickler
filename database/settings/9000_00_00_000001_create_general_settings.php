<?php

/** @noinspection PhpUnhandledExceptionInspection */

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->migrator->add('general.contact_name', config('stickler.contact_details.name'));
        $this->migrator->add('general.contact_address', config('stickler.contact_details.address'));
        $this->migrator->add('general.contact_city', config('stickler.contact_details.city'));
        $this->migrator->add('general.contact_country', config('stickler.contact_details.country'));
        $this->migrator->add('general.contact_company_name', config('stickler.contact_details.company_name'));
        $this->migrator->add('general.contact_company_number', config('stickler.contact_details.company_number'));
        $this->migrator->add('general.contact_email', config('stickler.contact_details.email'));
        $this->migrator->add('general.contact_phone', config('stickler.contact_details.phone'));
        $this->migrator->add('general.social_facebook', config('stickler.social_links.facebook'));
        $this->migrator->add('general.social_instagram', config('stickler.social_links.instagram'));
        $this->migrator->add('general.social_linkedin', config('stickler.social_links.linkedin'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->migrator->delete('general.contact_name');
        $this->migrator->delete('general.contact_address');
        $this->migrator->delete('general.contact_city');
        $this->migrator->delete('general.contact_country');
        $this->migrator->delete('general.contact_company_name');
        $this->migrator->delete('general.contact_company_number');
        $this->migrator->delete('general.contact_email');
        $this->migrator->delete('general.contact_phone');
        $this->migrator->delete('general.social_facebook');
        $this->migrator->delete('general.social_instagram');
        $this->migrator->delete('general.social_linkedin');
    }
};
