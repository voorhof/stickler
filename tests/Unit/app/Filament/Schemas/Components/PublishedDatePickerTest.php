<?php

use App\Filament\Schemas\Components\PublishedDatePicker;
use Filament\Forms\Components\DateTimePicker;

it('creates published date picker component', function () {
    $component = PublishedDatePicker::make();
    expect($component)->toBeInstanceOf(DateTimePicker::class)
        ->and($component->getName())->toBe('published_at');
});
