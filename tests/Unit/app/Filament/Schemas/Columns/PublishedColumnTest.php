<?php

use App\Filament\Schemas\Columns\PublishedColumn;
use Filament\Tables\Columns\TextColumn;

describe('column creation', function () {
    it('creates published column', function () {
        $column = PublishedColumn::make();
        expect($column)->toBeInstanceOf(TextColumn::class)
            ->and($column->getName())->toBe('published_at');
    });
});
