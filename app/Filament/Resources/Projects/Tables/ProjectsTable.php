<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Filament\Actions\HardDeleteBulkAction;
use App\Filament\Actions\PublishBulkAction;
use App\Filament\Actions\RestoreDeletedBulkAction;
use App\Filament\Actions\SoftDeleteBulkAction;
use App\Filament\Actions\UnpublishBulkAction;
use App\Filament\Filters\IsPublishedFilter;
use App\Filament\Filters\IsUnpublishedFilter;
use App\Filament\Schemas\Columns\CoverColumn;
use App\Filament\Schemas\Columns\CreatedColumn;
use App\Filament\Schemas\Columns\DeletedColumn;
use App\Filament\Schemas\Columns\IdColumn;
use App\Filament\Schemas\Columns\IsPublishedColumn;
use App\Filament\Schemas\Columns\OrderColumn;
use App\Filament\Schemas\Columns\PublishedColumn;
use App\Filament\Schemas\Columns\TitleColumn;
use App\Filament\Schemas\Columns\UpdatedColumn;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProjectsTable
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
                CoverColumn::make(),
                TitleColumn::make(),
                IsPublishedColumn::make(),
                PublishedColumn::make(),
                CreatedColumn::make(),
                UpdatedColumn::make(),
                DeletedColumn::make(),
            ])
            ->filters([
                IsPublishedFilter::make(),
                IsUnpublishedFilter::make(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    PublishBulkAction::make(),
                    UnpublishBulkAction::make(),
                    SoftDeleteBulkAction::make(),
                    HardDeleteBulkAction::make(),
                    RestoreDeletedBulkAction::make(),
                ]),
            ]);
    }
}
