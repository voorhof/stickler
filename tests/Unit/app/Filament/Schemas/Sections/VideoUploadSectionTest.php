<?php

use App\Filament\Schemas\Sections\VideoUploadSection;
use Filament\Schemas\Components\Section;

describe('section creation', function () {
    it('creates video upload section', function () {
        $section = VideoUploadSection::make();
        expect($section)->toBeInstanceOf(Section::class);
    });
});
