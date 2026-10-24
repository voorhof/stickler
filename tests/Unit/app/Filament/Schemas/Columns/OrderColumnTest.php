<?php

use App\Filament\Schemas\Columns\OrderColumn;
use Filament\Tables\Columns\TextColumn;

describe('column creation', function () {
    it('creates order column', function () {
        $column = OrderColumn::make();
        expect($column)->toBeInstanceOf(TextColumn::class)
            ->and($column->getName())->toBe('order_column');
    });
});
