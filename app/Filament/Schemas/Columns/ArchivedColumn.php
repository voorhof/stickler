<?php

namespace App\Filament\Schemas\Columns;

use Filament\Tables\Columns\TextColumn;

class ArchivedColumn
{
    public static function make(bool $isToggledHiddenByDefault = true): TextColumn
    {
        return TextColumn::make('archived_at')
            ->label(__('Archived at'))
            ->dateTime()
            ->toggleable(isToggledHiddenByDefault: $isToggledHiddenByDefault);
    }
}
