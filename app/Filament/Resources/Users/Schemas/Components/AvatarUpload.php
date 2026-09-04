<?php

namespace App\Filament\Resources\Users\Schemas\Components;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class AvatarUpload
{
    public static function make(): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make('avatar')
            ->collection('avatar')
            ->placeholder(fn (): string => str_replace('-', '_', app()->getLocale()) === 'nl_BE'
                ? 'Sleep je afbeelding hierheen of <span class="filepond--label-action">Blader</span>'
                : 'Drag & Drop your image or <span class="filepond--label-action">Browse</span>')
            ->maxSize(1024 * 2) // 2MB
            ->acceptedFileTypes(['image/*'])
            ->avatar()
            ->imageEditor();
    }
}
