<?php

use App\Filament\Schemas\Columns\UpdatedColumn;
use Filament\Tables\Columns\TextColumn;

it('creates updated column', function () {
    $column = UpdatedColumn::make();
    expect($column)->toBeInstanceOf(TextColumn::class)
        ->and($column->getName())->toBe('updated_at');
});
