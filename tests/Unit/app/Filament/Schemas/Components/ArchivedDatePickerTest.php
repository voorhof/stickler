<?php

use App\Filament\Schemas\Components\ArchivedDatePicker;
use Filament\Forms\Components\DateTimePicker;

it('creates archived date picker', function () {
    $component = ArchivedDatePicker::make();
    expect($component)->toBeInstanceOf(DateTimePicker::class)
        ->and($component->getName())->toBe('archived_at');
});
