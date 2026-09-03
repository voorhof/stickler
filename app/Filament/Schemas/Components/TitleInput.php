<?php

namespace App\Filament\Schemas\Components;

use Filament\Forms\Components\TextInput;

class TitleInput
{
    public static function make(
        int $maxLength = 128,
        string $label = 'Title',
        bool $isDistinct = false,
    ): TextInput {
        return TextInput::make('title')
            ->label(__($label))
            ->required()
            ->maxLength($maxLength)
            ->trim()
            ->distinct($isDistinct)
            ->columnSpanFull();
    }
}
