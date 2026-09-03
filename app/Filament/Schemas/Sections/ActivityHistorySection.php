<?php

namespace App\Filament\Schemas\Sections;

use App\Filament\Resources\Activities\ActivityResource;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class ActivityHistorySection
{
    public static function make(
        ?callable $state = null,
    ): Section {
        return Section::make(__('Activity history'))
            ->icon(Heroicon::OutlinedArchiveBox)
            ->hiddenOn('create')
            ->hidden(fn ($record) => ! $record?->activitiesAsSubject->count())
            ->collapsible()
            ->collapsed()
            ->persistCollapsed()
            ->schema([
                RepeatableEntry::make('activitiesAsSubject')
                    ->label(__('Activity history'))
                    ->hiddenLabel()
                    ->contained(false)
                    ->state(
                        $state
                        ?? fn ($record) => $record?->activitiesAsSubject()->latest()->get(),
                    )
                    ->schema([
                        TextEntry::make('title')
                            ->label(__('Activity history'))
                            ->hiddenLabel()
                            ->formatStateUsing(fn (string $state, $record): string => "$record->created_at - $state ")
                            ->url(fn ($record): string => ActivityResource::getUrl('view', ['record' => $record])),
                    ]),
            ]);
    }
}
