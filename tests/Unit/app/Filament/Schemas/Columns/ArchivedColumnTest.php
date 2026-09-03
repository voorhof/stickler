<?php

use App\Filament\Schemas\Columns\ArchivedColumn;
use Filament\Tables\Columns\TextColumn;

it('creates archived column', function () {
    $column = ArchivedColumn::make();
    expect($column)->toBeInstanceOf(TextColumn::class)
        ->and($column->getName())->toBe('archived_at');
});
