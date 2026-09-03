<?php

namespace App\Filament\Resources\Users\Schemas\Components;

use Filament\Forms\Components\TextInput;

class NameInput
{
    public static function make(): TextInput
    {
        return TextInput::make('name')
            ->label(__('Name'))
            ->required()
            ->maxLength(64)
            ->trim();
    }
}
