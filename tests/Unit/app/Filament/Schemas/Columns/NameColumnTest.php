<?php

use App\Filament\Schemas\Columns\NameColumn;
use Filament\Tables\Columns\TextColumn;

it('creates name column', function () {
    $column = NameColumn::make();
    expect($column)->toBeInstanceOf(TextColumn::class)
        ->and($column->getName())->toBe('name');
});
