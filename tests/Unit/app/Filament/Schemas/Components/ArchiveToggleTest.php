<?php

use App\Filament\Schemas\Components\ArchiveToggle;
use Filament\Forms\Components\Toggle;

it('creates archive toggle', function () {
    $component = ArchiveToggle::make('Post');
    expect($component)->toBeInstanceOf(Toggle::class)
        ->and($component->getName())->toBe('archive');
});
