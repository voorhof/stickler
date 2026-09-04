<?php

namespace App\Filament\Schemas\Columns;

use Filament\Tables\Columns\TextColumn;

class UpdatedColumn
{
    public static function make(bool $isToggledHiddenByDefault = true): TextColumn
    {
        return TextColumn::make('updated_at')
            ->label(__('Updated at'))
            ->dateTime()
            ->toggleable(isToggledHiddenByDefault: $isToggledHiddenByDefault);
    }
}
