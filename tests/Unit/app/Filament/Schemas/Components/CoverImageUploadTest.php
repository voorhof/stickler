<?php

use App\Filament\Schemas\Components\CoverImageUpload;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

describe('component creation', function () {
    it('creates cover image upload component', function () {
        $component = CoverImageUpload::make();
        expect($component)->toBeInstanceOf(SpatieMediaLibraryFileUpload::class)
            ->and($component->getName())->toBe('cover');
    });
});
