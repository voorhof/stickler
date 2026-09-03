<?php

namespace App\Filament\Schemas\Components;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class CoverImageUpload
{
    public static function make(
        string $label = 'Cover image',
        int $maxSize = 2048, // 2MB
        string $aspectRatio = '1:1',
    ): SpatieMediaLibraryFileUpload {
        return SpatieMediaLibraryFileUpload::make('cover')
            ->label(__($label))
            ->belowLabel(__('Maximum file size: :size.', ['size' => ($maxSize / 1024).'MB']))
            ->belowContent(__('This image will be used as the main image for overview pages. It is recommended to use a :ratio aspect ratio or similar landscape image.', ['ratio' => $aspectRatio]))
            ->collection('cover')
            ->placeholder(fn (): string => str_replace('-', '_', app()->getLocale()) === 'nl_BE'
                ? 'Sleep je afbeelding hierheen of <span class="filepond--label-action">Blader</span>'
                : 'Drag & Drop your image or <span class="filepond--label-action">Browse</span>')
            ->image()
            ->maxSize($maxSize)
            ->acceptedFileTypes(['image/*'])
            ->imageEditor()
            ->imageEditorAspectRatioOptions([
                $aspectRatio,
            ])
            ->columnSpanFull();
    }
}
