<?php

namespace App\Filament\Resources\Roles\Schemas\Sections;

use App\Models\Role;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Operation;
use Filament\Support\Icons\Heroicon;

class DetailsSection
{
    public static function make(): Section
    {
        return Section::make(__('Details'))
            ->icon(Heroicon::OutlinedDocumentText)
            ->collapsible()
            ->persistCollapsed(function ($operation): bool {
                return $operation !== Operation::Create->value;
            })
            ->schema([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->unique()
                    ->maxLength(64)
                    ->disabled(fn (?Role $record) => $record?->name === 'Admin'),
                TextInput::make('guard_name')
                    ->label(__('Guard'))
                    ->required()
                    ->maxLength(64)
                    ->default('web')
                    ->disabled(),
            ]);
    }
}
