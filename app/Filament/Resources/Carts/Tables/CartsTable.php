<?php

namespace App\Filament\Resources\Carts\Tables;

use App\Filament\Actions\HardDeleteBulkAction;
use App\Filament\Schemas\Columns\CreatedColumn;
use App\Filament\Schemas\Columns\IdColumn;
use App\Filament\Schemas\Columns\UpdatedColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                IdColumn::make(),
                TextColumn::make('cart_token')
                    ->label(__('Cart token'))
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label(__('Customer'))
                    ->toggleable(),
                IconColumn::make('is_guest')
                    ->label(__('Guest'))
                    ->boolean()
                    ->trueColor('warning')
                    ->falseColor('success'),
                TextColumn::make('items_count')
                    ->label(__('Items'))
                    ->badge()
                    ->state(fn ($record): int => $record->items_count ?? $record->cartItems()->count()),
                TextColumn::make('total')
                    ->label(__('Total'))
                    ->money('EUR'),
                IconColumn::make('isExpired')
                    ->label(__('Expired'))
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success'),
                TextColumn::make('expires_at')
                    ->label(__('Expires at'))
                    ->dateTime('M j, Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
                CreatedColumn::make(),
                UpdatedColumn::make(),
            ])
            ->filters([
                SelectFilter::make('is_guest')
                    ->label(__('Guest'))
                    ->options([
                        true => __('Guest'),
                        false => __('Registered'),
                    ]),
                SelectFilter::make('user_id')
                    ->label(__('Customer'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    HardDeleteBulkAction::make(),
                ]),
            ]);
    }
}
