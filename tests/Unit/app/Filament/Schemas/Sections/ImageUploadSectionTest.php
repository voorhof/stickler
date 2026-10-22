<?php

use App\Filament\Schemas\Sections\ImageUploadSection;
use Filament\Schemas\Components\Section;

describe('section creation', function () {
    it('creates image upload section', function () {
        $section = ImageUploadSection::make();
        expect($section)->toBeInstanceOf(Section::class);
    });
});
