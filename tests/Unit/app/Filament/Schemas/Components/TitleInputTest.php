<?php

use App\Filament\Schemas\Components\TitleInput;
use Filament\Forms\Components\TextInput;

it('creates title input component', function () {
    $component = TitleInput::make();
    expect($component)->toBeInstanceOf(TextInput::class)
        ->and($component->getName())->toBe('title');
});
