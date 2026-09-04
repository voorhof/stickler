<?php

use App\Filament\Schemas\Sections\ContentSection;
use Filament\Schemas\Components\Section;

it('creates content section', function () {
    $section = ContentSection::make();
    expect($section)->toBeInstanceOf(Section::class);
});
