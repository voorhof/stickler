<?php

use App\Filament\Schemas\Columns\EmailColumn;
use Filament\Tables\Columns\TextColumn;

it('creates email column', function () {
    $column = EmailColumn::make();
    expect($column)->toBeInstanceOf(TextColumn::class)
        ->and($column->getName())->toBe('email');
});
