<?php

namespace App\Filament\Resources\Activities\Schemas\Sections;

use App\Filament\Traits\FormatsJsonState;
use App\Models\Activity;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Operation;
use Filament\Support\Icons\Heroicon;

class PropertiesSection
{
    use FormatsJsonState;

    public static function make(): Section
    {
        return Section::make(__('Properties'))
            ->icon(Heroicon::OutlinedInformationCircle)
            ->collapsible()
            ->persistCollapsed(function ($operation): bool {
                return $operation !== Operation::Create->value;
            })
            ->columns()
            ->schema([
                TextEntry::make('properties')
                    ->label(__('Properties'))
                    ->hiddenLabel()
                    ->placeholder('-')
                    ->columnSpanFull()
                    ->prose()
                    ->markdown()
                    // Return ONE string so Filament does not iterate per element
                    ->state(fn (Activity $record): ?string => static::formatJsonState($record->properties)),
            ]);
    }
}
