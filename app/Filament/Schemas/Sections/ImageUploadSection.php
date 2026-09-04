<?php

namespace App\Filament\Schemas\Sections;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class ImageUploadSection
{
    public static function make(
        string $description = 'These optional images are displayed in a gallery below the main content.',
        array|string $hiddenOn = [],
    ): Section {
        return Section::make(__('Images'))
            ->icon(Heroicon::OutlinedPhoto)
            ->description(__($description))
            ->hiddenOn($hiddenOn)
            ->collapsible()
            ->collapsed()
            ->persistCollapsed()
            ->schema([
                SpatieMediaLibraryFileUpload::make('images')
                    ->label(__('Images'))
                    ->hiddenLabel()
                    ->collection('images')
                    ->placeholder(fn (): string => str_replace('-', '_', app()->getLocale()) === 'nl_BE'
                        ? 'Sleep je afbeeldingen hierheen of <span class="filepond--label-action">Blader</span>'
                        : 'Drag & Drop your images or <span class="filepond--label-action">Browse</span>')
                    ->image()
                    ->acceptedFileTypes(['image/*'])
                    ->imageEditor()
                    ->multiple()
                    ->reorderable()
                    ->maxSize(1024 * 2) // 2MB
                    ->responsiveImages()
                    ->columnSpanFull(),
            ]);
    }
}
