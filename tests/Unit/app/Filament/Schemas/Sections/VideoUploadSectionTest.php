<?php

use App\Filament\Schemas\Sections\VideoUploadSection;
use Filament\Schemas\Components\Section;

it('creates video upload section', function () {
    $section = VideoUploadSection::make();
    expect($section)->toBeInstanceOf(Section::class);
});
