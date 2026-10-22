<?php

use App\Filament\Schemas\Columns\NameColumn;
use Filament\Tables\Columns\TextColumn;

describe('column creation', function () {
    it('creates name column', function () {
        $column = NameColumn::make();
        expect($column)->toBeInstanceOf(TextColumn::class)
            ->and($column->getName())->toBe('name');
    });
});
