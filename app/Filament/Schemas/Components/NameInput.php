<?php

namespace App\Filament\Schemas\Components;

use Filament\Forms\Components\TextInput;

class NameInput
{
    public static function make(int $maxLength = 32): TextInput
    {
        return TextInput::make('name')
            ->label(__('Naam'))
            ->required()
            ->maxLength($maxLength)
            ->trim()
            ->formatStateUsing(fn ($record): ?string => $record?->name ? $record?->getTranslation('name', app()->getLocale()) : null)
            ->columnSpanFull();
    }
}
