<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace App\Filament\Schemas\Columns;

use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Model;

class IsPublishedColumn
{
    public static function make(): IconColumn
    {
        return IconColumn::make('published')
            ->label(__('Published'))
            ->sortable(['published_at'])
            ->color(fn (Model $record): string => match (true) {
                $record->published_at?->isFuture() => 'warning',
                $record->published => 'success',
                default => 'danger',
            });
    }
}
