<?php

namespace App\Filament\Resources\Users\Schemas\Sections;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Operation;
use Filament\Support\Icons\Heroicon;

class RoleSection
{
    public static function make(): Section
    {
        return Section::make(__('Role'))
            ->icon(Heroicon::OutlinedShieldCheck)
            ->collapsible()
            ->persistCollapsed(function ($operation): bool {
                return $operation !== Operation::Create->value;
            })
            ->schema([
                Grid::make()->schema([
                    Select::make('roles')
                        ->relationship('roles', 'name')
                        ->label(__('Choose role'))
                        ->hiddenLabel()
                        ->visible(fn (): bool => auth()->check() && auth()->user()->can('update roles')),
                ]),
            ]);
    }
}
