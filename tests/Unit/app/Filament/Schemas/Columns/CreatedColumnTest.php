<?php

use App\Filament\Schemas\Columns\CreatedColumn;
use Filament\Tables\Columns\TextColumn;

it('creates created column', function () {
    $column = CreatedColumn::make();
    expect($column)->toBeInstanceOf(TextColumn::class)
        ->and($column->getName())->toBe('created_at');
});
