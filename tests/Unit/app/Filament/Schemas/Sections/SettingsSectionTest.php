<?php

use App\Filament\Schemas\Sections\SettingsSection;
use Filament\Schemas\Components\Section;

describe('section creation', function () {
    it('creates settings section', function () {
        $section = SettingsSection::make();
        expect($section)->toBeInstanceOf(Section::class);
    });
});
