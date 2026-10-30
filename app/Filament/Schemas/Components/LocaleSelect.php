<?php

namespace App\Filament\Schemas\Components;

use Filament\Forms\Components\Select;

class LocaleSelect
{
    public static function make(): Select
    {
        $locales = config('app.supported_locales', []);

        return Select::make('locale')
            ->label(__('Language'))
            ->options(array_combine($locales, $locales))
            ->default('nl_BE')
            ->required()
            ->native(false);
    }
}
