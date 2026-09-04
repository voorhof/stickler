<?php

namespace App\Filament\Resources\Users\Schemas\Components;

use Filament\Forms\Components\TextInput;

class EmailInput
{
    public static function make(): TextInput
    {
        return TextInput::make('email')
            ->label(__('Email address'))
            ->email()
            ->inputMode('email')
            ->unique()
            ->required()
            ->maxLength(255)
            ->trim()
            ->autocomplete(false);
    }
}
