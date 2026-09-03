<?php

use App\Filament\Schemas\Columns\TitleColumn;
use Filament\Tables\Columns\TextColumn;

it('creates title column', function () {
    $column = TitleColumn::make();
    expect($column)->toBeInstanceOf(TextColumn::class)
        ->and($column->getName())->toBe('title');
});
