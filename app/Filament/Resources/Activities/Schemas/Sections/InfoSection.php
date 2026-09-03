<?php

/** @noinspection LaravelUnknownRouteNameInspection */

namespace App\Filament\Resources\Activities\Schemas\Sections;

use App\Filament\Traits\FormatsModelType;
use App\Models\Activity;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Operation;
use Filament\Support\Icons\Heroicon;

class InfoSection
{
    use FormatsModelType;

    public static function make(): Section
    {
        return Section::make(__('Information'))
            ->icon(Heroicon::OutlinedInformationCircle)
            ->collapsible()
            ->persistCollapsed(function ($operation): bool {
                return $operation !== Operation::Create->value;
            })
            ->columns(3)
            ->schema([
                TextEntry::make('log_name')
                    ->label(__('Log name'))
                    ->placeholder('-'),
                TextEntry::make('event')
                    ->label(__('Action'))
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->label(__('Description'))
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('subject.title')
                    ->label(__('Title'))
                    ->hidden(fn ($record) => $record->log_name === 'general_settings'
                        || $record->log_name === 'services_settings'
                        || $record->log_name === 'terms_settings')
                    ->placeholder('-')
                    ->url(fn (Activity $record) => $record->subject ? route($record->activityModelRoute($record->subject_type), ['record' => $record->subject]) : null)
                    ->color('primary'),
                TextEntry::make('subject.title')
                    ->label(__('Title'))
                    ->hidden(fn ($record) => $record->log_name !== 'general_settings'
                        && $record->log_name !== 'services_settings'
                        && $record->log_name !== 'terms_settings')
                    ->placeholder('-')
                    ->state(fn ($record) => __(str($record->log_name)
                        ->replace('_', ' ')
                        ->replace('terms settings', 'terms and policies')
                        ->ucfirst()
                        ->toString()))
                    ->url(fn ($record) => $record->log_name === 'general_settings'
                        ? route('filament.admin.pages.general-settings')
                        : ($record->log_name === 'services_settings' ? route('filament.admin.pages.services-settings')
                            : ($record->log_name === 'terms_settings' ? route('filament.admin.pages.terms-settings')
                                : null)))
                    ->color('primary'),
                TextEntry::make('subject_type')
                    ->label(__('Subject'))
                    ->formatStateUsing(fn (?string $state): string => self::formatModelType(type: $state))
                    ->placeholder('-'),
                TextEntry::make('subject_id')
                    ->label(__('Subject ID'))
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('causer.title')
                    ->label(__('Causer'))
                    ->placeholder('-')
                    ->url(fn (Activity $record) => $record->causer ? route($record->activityModelRoute($record->causer_type), ['record' => $record->causer]) : null)
                    ->color('primary'),
                TextEntry::make('causer_type')
                    ->label(__('Causer'))
                    ->formatStateUsing(fn (?string $state): string => self::formatModelType(type: $state))
                    ->placeholder('-'),
                TextEntry::make('causer_id')
                    ->label(__('Causer ID'))
                    ->numeric()
                    ->placeholder('-'),
            ]);
    }
}
