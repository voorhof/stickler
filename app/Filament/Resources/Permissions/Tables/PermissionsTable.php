<?php

namespace App\Filament\Resources\Permissions\Tables;

use App\Filament\Schemas\Columns\CreatedColumn;
use App\Filament\Schemas\Columns\IdColumn;
use App\Filament\Schemas\Columns\NameColumn;
use App\Filament\Schemas\Columns\UpdatedColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id')
            ->columns([
                IdColumn::make(),
                NameColumn::make(),
                TextColumn::make('guard_name')
                    ->label(__('Guard')),
                CreatedColumn::make(),
                UpdatedColumn::make(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
