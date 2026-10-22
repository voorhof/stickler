<?php

use App\Filament\Schemas\Sections\ContentSection;
use Filament\Schemas\Components\Section;

describe('section creation', function () {
    it('creates content section', function () {
        $section = ContentSection::make();
        expect($section)->toBeInstanceOf(Section::class);
    });
});
