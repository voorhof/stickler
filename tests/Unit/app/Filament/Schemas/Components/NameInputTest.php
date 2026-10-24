<?php

use App\Filament\Schemas\Components\NameInput;
use Filament\Forms\Components\TextInput;

describe('component creation', function () {
    it('creates name input component', function () {
        $component = NameInput::make();
        expect($component)->toBeInstanceOf(TextInput::class)
            ->and($component->getName())->toBe('name');
    });
});
