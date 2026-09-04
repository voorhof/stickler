<?php

namespace App\Filament\Schemas\Columns;

use Filament\Tables\Columns\TextColumn;

class IdColumn
{
    public static function make(): TextColumn
    {
        return TextColumn::make('id')
            ->label(__('ID'))
            ->numeric()
            ->toggleable(isToggledHiddenByDefault: true);
    }
}
