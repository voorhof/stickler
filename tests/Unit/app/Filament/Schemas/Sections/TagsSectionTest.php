<?php

use App\Filament\Schemas\Sections\TagsSection;
use Filament\Schemas\Components\Section;

it('creates tags section', function () {
    $section = TagsSection::make();
    expect($section)->toBeInstanceOf(Section::class);
});
