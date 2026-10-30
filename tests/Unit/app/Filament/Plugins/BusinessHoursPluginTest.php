<?php

use App\Filament\Plugins\BusinessHoursPlugin;
use LaBoiteACode\FilamentBusinessHours\FilamentBusinessHoursPlugin;

it('extends the business hours plugin', function () {
    expect(BusinessHoursPlugin::make())->toBeInstanceOf(FilamentBusinessHoursPlugin::class);
});

it('keeps the business hours plugin id', function () {
    expect(BusinessHoursPlugin::make()->getId())->toBe('filament-business-hours');
});

it('resolves the navigation group to the translated Settings group', function () {
    expect(BusinessHoursPlugin::make()->getNavigationGroup())->toBe(__('Settings'));
});

it('ignores a navigation group set through the fluent API', function () {
    $plugin = BusinessHoursPlugin::make()->navigationGroup('Shop');

    expect($plugin->getNavigationGroup())->toBe(__('Settings'));
});

it('translates the navigation group at render time', function () {
    $originalLocale = app()->getLocale();

    try {
        app()->setLocale('en');
        $englishGroup = BusinessHoursPlugin::make()->getNavigationGroup();

        app()->setLocale('nl');
        $dutchGroup = BusinessHoursPlugin::make()->getNavigationGroup();
    } finally {
        app()->setLocale($originalLocale);
    }

    expect($englishGroup)->toBe('Settings')
        ->and($dutchGroup)->toBe('Instellingen');
});
