<?php

namespace App\Filament\Resources\Roles\Schemas\Sections;

use App\Models\Permission;
use App\Models\Role;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Operation;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class PermissionsSection
{
    public static function make(): Section
    {
        return Section::make(__('Permissions'))
            ->icon(Heroicon::OutlinedShieldExclamation)
            ->collapsible()
            ->persistCollapsed(function ($operation): bool {
                return $operation !== Operation::Create->value;
            })
            ->schema([
                Grid::make(0)
                    ->schema([
                        CheckboxList::make('permissions')
                            ->label(__('Permissions'))
                            ->hiddenLabel()
                            // ->relationship(titleAttribute: 'name')
                            // We are using the options() method her instead of relationship(), to allow sorting by last word of permission name
                            ->options(function () {
                                return Permission::all()
                                    ->sortBy(fn ($permission) => Str::afterLast($permission->name, ' ').' '.$permission->name)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->saveRelationshipsUsing(function ($component, $state) {
                                $component->getRecord()->permissions()->sync($state);
                            })
                            ->afterStateHydrated(function (CheckboxList $component, ?Role $record) {
                                $component->state($record?->permissions()->pluck('id')->toArray() ?? []);
                            })
                            ->columns(4)
                            ->searchable()
                            ->bulkToggleable(),
                    ]),
            ]);
    }
}
