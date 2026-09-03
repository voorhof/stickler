<?php

use App\Filament\Schemas\Components\SlugInput;
use Filament\Forms\Components\TextInput;

it('creates slug input component', function () {
    $component = SlugInput::make();
    expect($component)->toBeInstanceOf(TextInput::class)
        ->and($component->getName())->toBe('slug');
});
