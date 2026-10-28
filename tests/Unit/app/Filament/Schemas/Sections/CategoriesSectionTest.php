<?php

use App\Filament\Schemas\Sections\CategoriesSection;
use Filament\Schemas\Components\Section;

describe('section creation', function () {
    it('creates categories section', function () {
        $section = CategoriesSection::make();
        expect($section)->toBeInstanceOf(Section::class);
    });

    it('creates categories section with forModel parameter', function () {
        $section = CategoriesSection::make('for_products');
        expect($section)->toBeInstanceOf(Section::class);
    });
});
