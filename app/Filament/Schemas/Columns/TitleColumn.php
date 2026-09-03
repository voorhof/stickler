<?php

namespace App\Filament\Schemas\Columns;

use Filament\Tables\Columns\TextColumn;

class TitleColumn
{
    public static function make(
        string $column = 'title',
        string $label = 'Title',
        int $limit = 48,
    ): TextColumn {
        return TextColumn::make($column)
            ->label(__($label))
            ->limit($limit);
    }
}
