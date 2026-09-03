<?php

use App\Filament\Schemas\Columns\PublishedColumn;
use Filament\Tables\Columns\TextColumn;

it('creates published column', function () {
    $column = PublishedColumn::make();
    expect($column)->toBeInstanceOf(TextColumn::class)
        ->and($column->getName())->toBe('published_at');
});
