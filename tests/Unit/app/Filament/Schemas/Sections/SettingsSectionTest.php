<?php

use App\Filament\Schemas\Sections\SettingsSection;
use Filament\Schemas\Components\Section;

it('creates settings section', function () {
    $section = SettingsSection::make();
    expect($section)->toBeInstanceOf(Section::class);
});
