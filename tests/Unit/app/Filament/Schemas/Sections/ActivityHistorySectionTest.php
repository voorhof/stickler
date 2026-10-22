<?php

use App\Filament\Schemas\Sections\ActivityHistorySection;
use Filament\Schemas\Components\Section;

describe('section creation', function () {
    it('creates activity history section', function () {
        $section = ActivityHistorySection::make();
        expect($section)->toBeInstanceOf(Section::class);
    });
});
