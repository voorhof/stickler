<?php

namespace App\Filament\Schemas\Columns;

use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class CoverColumn
{
    public static function make(
        string $label = 'Cover',
    ): SpatieMediaLibraryImageColumn {
        return SpatieMediaLibraryImageColumn::make('cover')
            ->collection('cover')
            ->label(__($label))
            ->conversion('thumb')
            ->imageSize(40);
    }
}
