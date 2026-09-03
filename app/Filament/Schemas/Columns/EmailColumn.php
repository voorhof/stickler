<?php

namespace App\Filament\Schemas\Columns;

use Filament\Tables\Columns\TextColumn;

class EmailColumn
{
    public static function make(): TextColumn
    {
        return TextColumn::make('email')
            ->label(__('Email address'));
    }
}
