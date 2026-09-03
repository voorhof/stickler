<?php

namespace App\Filament\Resources\Media\Tables;

use App\Filament\Actions\HardDeleteBulkAction;
use App\Filament\Schemas\Columns\CreatedColumn;
use App\Filament\Schemas\Columns\IdColumn;
use App\Filament\Schemas\Columns\NameColumn;
use App\Filament\Schemas\Columns\OrderColumn;
use App\Filament\Schemas\Columns\UpdatedColumn;
use App\Models\Media;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->reorderable('order_column', direction: 'asc')
            ->reorderRecordsTriggerAction(
                fn (Action $action, bool $isReordering) => $action
                    ->button()
                    ->label($isReordering ? __('Disable reordering') : ''),
            )
            ->columns([
                OrderColumn::make(),
                IdColumn::make(),
                ImageColumn::make('preview_url')
                    ->label(__('Preview'))
                    ->imageSize(100),
                NameColumn::make(),
                TextColumn::make('mime_type')
                    ->label(__('Mime type')),
                TextColumn::make('file_name')
                    ->label(__('File name'))
                    ->limit(64)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('size')
                    ->label(__('Size'))
                    ->numeric(),
                TextColumn::make('collection_name')
                    ->label(__('Collection')),
                TextColumn::make('model_type')
                    ->label(__('Model type'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('model_id')
                    ->label(__('Model ID'))
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('uuid')
                    ->label(__('UUID'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('disk')
                    ->label(__('Disk'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('conversions_disk')
                    ->label(__('Conversions disk'))
                    ->toggleable(isToggledHiddenByDefault: true),
                CreatedColumn::make(
                    isToggledHiddenByDefault: false,
                ),
                UpdatedColumn::make(),
            ])
            ->filters([
                SelectFilter::make('mime_type')
                    ->label(__('Mime type'))
                    ->options(fn () => Media::distinct()
                        ->pluck('mime_type', 'mime_type')
                        ->toArray(),
                    ),
                SelectFilter::make('collection_name')
                    ->label(__('Collection'))
                    ->options(fn () => Media::distinct()
                        ->pluck('collection_name', 'collection_name')
                        ->toArray(),
                    ),
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
