<?php

namespace App\Filament\Schemas\Components;

use Filament\Forms\Components\DateTimePicker;

class PublishedDatePicker
{
    public static function make(): DateTimePicker
    {
        return DateTimePicker::make('published_at')
            ->label(__('Published at'))
            ->placeholder(now());
    }
}
