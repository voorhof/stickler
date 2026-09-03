<?php

use App\Filament\Schemas\Sections\DatabaseInfoSection;
use Filament\Schemas\Components\Section;

it('creates database info section', function () {
    $section = DatabaseInfoSection::make();
    expect($section)->toBeInstanceOf(Section::class);
});
