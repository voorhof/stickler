<?php

namespace App\Filament\Schemas\Columns;

use Filament\Tables\Columns\TextColumn;

class PublishedColumn
{
    public static function make(bool $isToggledHiddenByDefault = true): TextColumn
    {
        return TextColumn::make('published_at')
            ->label(__('Published at'))
            ->dateTime()
            ->toggleable(isToggledHiddenByDefault: $isToggledHiddenByDefault);
    }
}
