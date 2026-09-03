<?php

use App\Filament\Schemas\Components\OrderColumnInput;
use Filament\Forms\Components\TextInput;

it('creates order column input component', function () {
    $component = OrderColumnInput::make();
    expect($component)->toBeInstanceOf(TextInput::class)
        ->and($component->getName())->toBe('order_column');
});
