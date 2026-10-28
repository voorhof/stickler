<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Actions\HardDeleteBulkAction;
use App\Filament\Actions\RestoreDeletedBulkAction;
use App\Filament\Actions\SoftDeleteBulkAction;
use App\Filament\Schemas\Columns\CreatedColumn;
use App\Filament\Schemas\Columns\DeletedColumn;
use App\Filament\Schemas\Columns\IdColumn;
use App\Filament\Schemas\Columns\UpdatedColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                IdColumn::make(),
                TextColumn::make('order_number')
                    ->label(__('Order number'))
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('user.name')
                    ->label(__('Customer'))
                    ->getStateUsing(fn ($record): string => $record->customerName())
                    ->toggleable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (OrderStatus $state): string => match ($state) {
                        OrderStatus::Pending => 'warning',
                        OrderStatus::Confirmed => 'info',
                        OrderStatus::Processing => 'primary',
                        OrderStatus::Shipped, OrderStatus::Delivered => 'success',
                        OrderStatus::Cancelled => 'danger',
                    }),
                TextColumn::make('payment_status')
                    ->label(__('Payment status'))
                    ->badge()
                    ->color(fn (PaymentStatus $state): string => match ($state) {
                        PaymentStatus::Unpaid => 'warning',
                        PaymentStatus::Paid => 'success',
                        PaymentStatus::Refunded => 'danger',
                    }),
                TextColumn::make('total')
                    ->label(__('Total'))
                    ->money('EUR'),
                TextColumn::make('items_count')
                    ->label(__('Items'))
                    ->badge()
                    ->state(fn ($record): int => $record->items_count ?? $record->items()->count())
                    ->toggleable(isToggledHiddenByDefault: true),
                CreatedColumn::make(),
                UpdatedColumn::make(),
                DeletedColumn::make(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(OrderStatus::class),
                SelectFilter::make('payment_status')
                    ->label(__('Payment status'))
                    ->options(PaymentStatus::class),
                SelectFilter::make('user_id')
                    ->label(__('Customer'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    SoftDeleteBulkAction::make(),
                    HardDeleteBulkAction::make(),
                    RestoreDeletedBulkAction::make(),
                ]),
            ]);
    }
}
