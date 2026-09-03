<?php

namespace App\Filament\Schemas\Columns;

use Filament\Tables\Columns\TextColumn;

class NameColumn
{
    public static function make(int $limit = 48, string $label = 'Name'): TextColumn
    {
        return TextColumn::make('name')
            ->label(__($label))
            ->limit($limit);
    }
}
