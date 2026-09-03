<?php

use App\Filament\Schemas\Columns\DeletedColumn;
use Filament\Tables\Columns\TextColumn;

it('creates deleted column', function () {
    $column = DeletedColumn::make();
    expect($column)->toBeInstanceOf(TextColumn::class)
        ->and($column->getName())->toBe('deleted_at');
});
