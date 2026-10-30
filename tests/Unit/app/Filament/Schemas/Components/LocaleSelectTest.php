<?php

use App\Filament\Schemas\Components\LocaleSelect;
use Filament\Forms\Components\Select;

describe('component creation', function () {
    it('creates a locale select component', function () {
        $component = LocaleSelect::make();

        expect($component)->toBeInstanceOf(Select::class)
            ->and($component->getName())->toBe('locale')
            ->and($component->isRequired())->toBeTrue()
            ->and($component->getDefaultState())->toBe('nl_BE');
    });

    it('offers every supported locale as an option', function () {
        $component = LocaleSelect::make();

        $expectedLocales = config('app.supported_locales');

        expect(array_keys($component->getOptions()))->toBe($expectedLocales)
            ->and($component->getOptions())->toBe(array_combine($expectedLocales, $expectedLocales));
    });

    it('does not offer the removed en_UK locale', function () {
        expect(array_keys(LocaleSelect::make()->getOptions()))->not->toContain('en_UK');
    });
});
