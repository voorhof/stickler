<?php

namespace App\Filament\Resources\Messages\Tables;

use App\Filament\Actions\ArchiveBulkAction;
use App\Filament\Actions\DearchiveBulkAction;
use App\Filament\Actions\HardDeleteBulkAction;
use App\Filament\Actions\RestoreDeletedBulkAction;
use App\Filament\Actions\SoftDeleteBulkAction;
use App\Filament\Filters\IsArchivedFilter;
use App\Filament\Filters\IsReadFilter;
use App\Filament\Filters\IsRepliedFilter;
use App\Filament\Filters\IsUnReadFilter;
use App\Filament\Schemas\Columns\ArchivedColumn;
use App\Filament\Schemas\Columns\CreatedColumn;
use App\Filament\Schemas\Columns\DeletedColumn;
use App\Filament\Schemas\Columns\EmailColumn;
use App\Filament\Schemas\Columns\IdColumn;
use App\Filament\Schemas\Columns\NameColumn;
use App\Filament\Schemas\Columns\OrderColumn;
use App\Filament\Schemas\Columns\TitleColumn;
use App\Filament\Schemas\Columns\UpdatedColumn;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class MessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('order_column')
            ->reorderable('order_column', direction: 'asc')
            ->reorderRecordsTriggerAction(
                fn (Action $action, bool $isReordering) => $action
                    ->button()
                    ->label($isReordering ? __('Disable reordering') : ''),
            )
            ->columns([
                OrderColumn::make(),
                IdColumn::make(),
                TitleColumn::make(
                    column: 'subject',
                    label: 'Subject',
                ),
                NameColumn::make(
                    label: 'Sender',
                ),
                EmailColumn::make(),
                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('read')
                    ->label(__('Read')),
                IconColumn::make('replied')
                    ->label(__('Replied')),
                TextColumn::make('source')
                    ->label(__('Origin source'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('replied_at')
                    ->label(__('Replied at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                ArchivedColumn::make(),
                CreatedColumn::make(),
                UpdatedColumn::make(),
                DeletedColumn::make(),
            ])
            ->filters([
                IsUnReadFilter::make(),
                IsReadFilter::make(),
                IsRepliedFilter::make(),
                IsArchivedFilter::make(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ArchiveBulkAction::make(),
                    DearchiveBulkAction::make(),
                    SoftDeleteBulkAction::make(),
                    HardDeleteBulkAction::make(),
                    RestoreDeletedBulkAction::make(),
                ]),
            ]);
    }
}
