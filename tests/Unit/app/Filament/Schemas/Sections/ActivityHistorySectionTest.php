<?php

use App\Filament\Schemas\Sections\ActivityHistorySection;
use Filament\Schemas\Components\Section;

it('creates activity history section', function () {
    $section = ActivityHistorySection::make();
    expect($section)->toBeInstanceOf(Section::class);
});
