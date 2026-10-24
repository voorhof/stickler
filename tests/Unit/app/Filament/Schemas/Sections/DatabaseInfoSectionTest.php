<?php

use App\Filament\Schemas\Sections\DatabaseInfoSection;
use Filament\Schemas\Components\Section;

describe('section creation', function () {
    it('creates database info section', function () {
        $section = DatabaseInfoSection::make();
        expect($section)->toBeInstanceOf(Section::class);
    });
});
