<?php

namespace App\Filament\Resources\PageViews\Tables;

use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PageViewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('views', 'desc')
            ->columns([
                TextColumn::make('path')
                    ->label(__('Path'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('route_name')
                    ->label(__('Route name'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('views')
                    ->label(__('Page views'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unique_visitors')
                    ->label(__('Unique visitors'))
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('viewed_on')
                    ->label(__('Viewed on'))
                    ->schema([
                        DatePicker::make('viewed_from')
                            ->label(__('From')),
                        DatePicker::make('viewed_until')
                            ->label(__('Until')),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['viewed_from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('viewed_on', '>=', $date))
                        ->when($data['viewed_until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('viewed_on', '<=', $date))),
            ]);
    }
}
