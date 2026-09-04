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
        $this->migrator->add('terms.terms_and_conditions', '<h2>Algemene Voorwaarden</h2>');
        $this->migrator->add('terms.privacy_policy', '<h2>Privacy policy</h2>');
        $this->migrator->add('terms.cookie_policy', '<h2>Cookie policy</h2>');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->migrator->delete('terms.terms_and_conditions');
        $this->migrator->delete('terms.privacy_policy');
        $this->migrator->delete('terms.cookie_policy');
    }
};
