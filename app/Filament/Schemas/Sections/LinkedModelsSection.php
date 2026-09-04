<?php

namespace App\Filament\Schemas\Sections;

use App\Filament\Resources\Media\MediaResource;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class LinkedModelsSection
{
    public static function make(
        $resource,
        string $relationName,
        string $modelLabel,
        Heroicon $icon,
        string $url = 'edit',
        string $entryColumn = 'title',
        int $grid = 1,
    ): Section {
        return Section::make(__('Linked :model', ['model' => __($modelLabel)]))
            ->icon($icon)
            ->hiddenOn('create')
            ->hidden(fn ($record) => ! $record?->$relationName->count())
            ->collapsible()
            ->collapsed()
            ->persistCollapsed()
            ->schema([
                RepeatableEntry::make($relationName)
                    ->label(__($modelLabel))
                    ->hiddenLabel()
                    ->contained(false)
                    ->grid($grid)
                    ->schema([
                        TextEntry::make($entryColumn)
                            ->label(__($modelLabel))
                            ->hiddenLabel()
                            ->color('primary')
                            ->url(fn ($record): string => $resource::getUrl($url, ['record' => $record])),
                        ImageEntry::make('preview_url')
                            ->label(__('Preview'))
                            ->hidden(fn ($record) => ! $record->preview_url)
                            ->hiddenLabel()
                            ->imageSize(200)
                            ->extraImgAttributes(['loading' => 'lazy'])
                            ->visible(fn ($record) => str_starts_with($record->mime_type, 'image/'))
                            ->url(fn ($record): string => MediaResource::getUrl('edit', ['record' => $record])),
                    ]),
            ]);
    }
}
