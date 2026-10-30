<?php

namespace App\Filament\Plugins;

use LaBoiteACode\FilamentBusinessHours\FilamentBusinessHoursPlugin;

class BusinessHoursPlugin extends FilamentBusinessHoursPlugin
{
    /**
     * Resolved at render time, so the group matches the translated 'Settings' group of the other resources.
     */
    public function getNavigationGroup(): string
    {
        return __('Settings');
    }
}
