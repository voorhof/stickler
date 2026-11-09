<?php

namespace App\Filament\Resources\Users\Schemas\Sections;

use App\Models\Role;
use App\Models\User;
use Closure;
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
                        ->visible(fn (): bool => auth()->check() && auth()->user()->can('update roles'))
                        ->disabled(fn (?User $record): bool => $record && $record->hasRole('Admin') && User::role('Admin')->withoutTrashed()->where('id', '!=', $record->id)->count() === 0)
                        ->helperText(fn (?User $record): ?string => $record && $record->hasRole('Admin') && User::role('Admin')->withoutTrashed()->where('id', '!=', $record->id)->count() === 0 ? __('The role cannot be changed because this is the last remaining administrator.') : null)
                        ->rules([
                            fn (?User $record): Closure => function (string $attribute, $value, Closure $fail) use ($record): void {
                                if ($record && $record->hasRole('Admin') && User::role('Admin')->withoutTrashed()->where('id', '!=', $record->id)->count() === 0) {
                                    $adminRole = Role::where('name', 'Admin')->first();
                                    if ($adminRole && (int) $value !== (int) $adminRole->id) {
                                        $fail(__('The last remaining administrator cannot be demoted.'));
                                    }
                                }
                            },
                        ]),
                ]),
            ]);
    }
}
