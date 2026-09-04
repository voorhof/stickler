<?php

namespace App\Filament\Schemas\Components;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Utilities\Get;

class ArchivedDatePicker
{
    public static function make(): DateTimePicker
    {
        return DateTimePicker::make('archived_at')
            ->label(__('Archived at'))
            ->hiddenOn('create')
            ->visible(fn (Get $get): bool => $get('archive'))
            ->required(fn (Get $get): bool => $get('archive'))
            ->nullable(fn (Get $get): bool => ! $get('archive'));
    }
}
