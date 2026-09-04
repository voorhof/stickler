<?php

namespace App\Filament\Schemas\Sections;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class DocumentUploadSection
{
    public static function make(
        string $description = 'Upload optional PDF documents.',
        array|string $hiddenOn = [],
    ): Section {
        return Section::make(__('Documents'))
            ->icon(Heroicon::OutlinedDocument)
            ->description(__($description))
            ->hiddenOn($hiddenOn)
            ->collapsible()
            ->collapsed()
            ->persistCollapsed()
            ->schema([
                SpatieMediaLibraryFileUpload::make('documents')
                    ->label(__('Documents'))
                    ->hiddenLabel()
                    ->collection('documents')
                    ->acceptedFileTypes(['application/pdf'])
                    ->multiple()
                    ->reorderable()
                    ->maxSize(1024 * 50) // 50MB
                    ->columnSpanFull(),
            ]);
    }
}
