<?php

namespace App\Filament\Schemas\Columns;

use Filament\Tables\Columns\TextColumn;

class DeletedColumn
{
    public static function make(bool $isToggledHiddenByDefault = true): TextColumn
    {
        return TextColumn::make('deleted_at')
            ->label(__('Deleted at'))
            ->dateTime()
            ->toggleable(isToggledHiddenByDefault: $isToggledHiddenByDefault);
    }
}
