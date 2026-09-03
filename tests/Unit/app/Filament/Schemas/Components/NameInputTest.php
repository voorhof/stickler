<?php

use App\Filament\Schemas\Components\NameInput;
use Filament\Forms\Components\TextInput;

it('creates name input component', function () {
    $component = NameInput::make();
    expect($component)->toBeInstanceOf(TextInput::class)
        ->and($component->getName())->toBe('name');
});
