<?php

namespace App\Filament\Schemas\Columns;

use Filament\Tables\Columns\TextColumn;

class OrderColumn
{
    public static function make(): TextColumn
    {
        return TextColumn::make('order_column')
            ->label(__('Order column'))
            ->numeric()
            ->visible(fn ($livewire): bool => $livewire->isTableReordering())
            ->toggleable(false);
    }
}
