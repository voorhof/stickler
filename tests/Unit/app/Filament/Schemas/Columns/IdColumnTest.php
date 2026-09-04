<?php

use App\Filament\Schemas\Columns\IdColumn;
use Filament\Tables\Columns\TextColumn;

it('creates id column', function () {
    $column = IdColumn::make();
    expect($column)->toBeInstanceOf(TextColumn::class)
        ->and($column->getName())->toBe('id');
});
