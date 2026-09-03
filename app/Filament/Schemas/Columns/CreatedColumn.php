<?php

namespace App\Filament\Schemas\Columns;

use Filament\Tables\Columns\TextColumn;

class CreatedColumn
{
    public static function make(bool $isToggledHiddenByDefault = true): TextColumn
    {
        return TextColumn::make('created_at')
            ->label(__('Created at'))
            ->dateTime()
            ->toggleable(isToggledHiddenByDefault: $isToggledHiddenByDefault);
    }
}
