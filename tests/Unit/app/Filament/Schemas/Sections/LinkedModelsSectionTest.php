<?php

use App\Filament\Resources\Media\MediaResource;
use App\Filament\Schemas\Sections\LinkedModelsSection;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

it('creates linked models section', function () {
    $section = LinkedModelsSection::make(
        MediaResource::class,
        'media',
        'Media',
        Heroicon::OutlinedPhoto,
    );
    expect($section)->toBeInstanceOf(Section::class);
});
