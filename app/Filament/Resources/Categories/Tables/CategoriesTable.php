<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Filament\Actions\HardDeleteBulkAction;
use App\Filament\Actions\RestoreDeletedBulkAction;
use App\Filament\Actions\SoftDeleteBulkAction;
use App\Filament\Filters\ForModelsFilter;
use App\Filament\Schemas\Columns\CreatedColumn;
use App\Filament\Schemas\Columns\DeletedColumn;
use App\Filament\Schemas\Columns\IdColumn;
use App\Filament\Schemas\Columns\OrderColumn;
use App\Filament\Schemas\Columns\TitleColumn;
use App\Filament\Schemas\Columns\UpdatedColumn;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CategoriesTable
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
                TitleColumn::make(),
                IconColumn::make('for_pages')
                    ->label(__('For :model', ['model' => __('Pages')])),
                IconColumn::make('for_posts')
                    ->label(__('For :model', ['model' => __('Blogposts')])),
                IconColumn::make('for_products')
                    ->label(__('For :model', ['model' => __('Products')])),
                IconColumn::make('for_projects')
                    ->label(__('For :model', ['model' => __('Projects')])),
                CreatedColumn::make(),
                UpdatedColumn::make(),
                DeletedColumn::make(),
            ])
            ->filters([
                ForModelsFilter::make('for_pages')
                    ->modelName(__('Pages')),
                ForModelsFilter::make('for_posts')
                    ->modelName(__('Blogposts')),
                ForModelsFilter::make('for_products')
                    ->modelName(__('Products')),
                ForModelsFilter::make('for_projects')
                    ->modelName(__('Projects')),
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
