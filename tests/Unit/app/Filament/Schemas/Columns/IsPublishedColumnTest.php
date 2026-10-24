<?php

use App\Filament\Schemas\Columns\IsPublishedColumn;
use Filament\Tables\Columns\IconColumn;

describe('column creation', function () {
    it('creates is published column', function () {
        $column = IsPublishedColumn::make();
        expect($column)->toBeInstanceOf(IconColumn::class)
            ->and($column->getName())->toBe('published');
    });
});
