<?php

use App\Filament\Schemas\Sections\TagsSection;
use Filament\Schemas\Components\Section;

describe('section creation', function () {
    it('creates tags section', function () {
        $section = TagsSection::make();
        expect($section)->toBeInstanceOf(Section::class);
    });
});
