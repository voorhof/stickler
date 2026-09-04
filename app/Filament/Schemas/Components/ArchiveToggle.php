<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace App\Filament\Schemas\Components;

use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Model;

class ArchiveToggle
{
    public static function make(string $modelLabel): Toggle
    {
        return Toggle::make('archive')
            ->label(__('Archive :model', ['model' => __($modelLabel)]))
            ->live()
            ->afterStateHydrated(function (Toggle $component, ?Model $record) {
                if ($record && $record->archived_at) {
                    $component->state(true);
                } else {
                    $component->state(false);
                }
            })
            ->afterStateUpdated(function (Set $set, Get $get, bool $state, ?Model $record) {
                // Set the timestamp if it is currently empty and we are toggling on
                if ($state && ($get('archived_at') === null)) {
                    $set('archived_at', now());
                } else {
                    $set('archived_at', $record->archived_at ?? null);
                }
            })
            ->hiddenOn('create')
            ->columnSpanFull();
    }
}
