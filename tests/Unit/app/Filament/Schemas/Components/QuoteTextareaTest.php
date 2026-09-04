<?php

use App\Filament\Schemas\Components\QuoteTextarea;
use Filament\Forms\Components\Textarea;

it('creates quote textarea component', function () {
    $component = QuoteTextarea::make();
    expect($component)->toBeInstanceOf(Textarea::class)
        ->and($component->getName())->toBe('quote');
});
