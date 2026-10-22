<?php

use App\Filament\Schemas\Columns\TitleColumn;
use Filament\Tables\Columns\TextColumn;

describe('column creation', function () {
    it('creates title column', function () {
        $column = TitleColumn::make();
        expect($column)->toBeInstanceOf(TextColumn::class)
            ->and($column->getName())->toBe('title');
    });
});
