<?php

use App\Filament\Schemas\Sections\DocumentUploadSection;
use Filament\Schemas\Components\Section;

it('creates document upload section', function () {
    $section = DocumentUploadSection::make();
    expect($section)->toBeInstanceOf(Section::class);
});
