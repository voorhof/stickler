<?php

use App\Filament\Schemas\Columns\CoverColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

describe('column creation', function () {
    it('creates cover column', function () {
        $column = CoverColumn::make();
        expect($column)->toBeInstanceOf(SpatieMediaLibraryImageColumn::class)
            ->and($column->getName())->toBe('cover');
    });
});
