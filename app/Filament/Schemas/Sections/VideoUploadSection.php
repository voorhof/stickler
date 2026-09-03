<?php

namespace App\Filament\Schemas\Sections;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class VideoUploadSection
{
    public static function make(
        string $description = 'Upload optional videos.',
        array|string $hiddenOn = [],
    ): Section {
        return Section::make(__('Videos'))
            ->icon(Heroicon::OutlinedDocument)
            ->description(__($description))
            ->hiddenOn($hiddenOn)
            ->collapsible()
            ->collapsed()
            ->persistCollapsed()
            ->schema([
                SpatieMediaLibraryFileUpload::make('videos')
                    ->label(__('Videos'))
                    ->hiddenLabel()
                    ->collection('videos')
                    ->acceptedFileTypes(['video/mp4'])
                    ->multiple()
                    ->reorderable()
                    ->maxSize(1024 * 50) // 50MB
                    ->columnSpanFull(),
            ]);
    }
}
