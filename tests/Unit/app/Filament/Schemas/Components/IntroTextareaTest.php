<?php

use App\Filament\Schemas\Components\IntroTextarea;
use Filament\Forms\Components\Textarea;

it('creates intro textarea component', function () {
    $component = IntroTextarea::make();
    expect($component)->toBeInstanceOf(Textarea::class)
        ->and($component->getName())->toBe('intro');
});
