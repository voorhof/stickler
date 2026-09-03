<?php

namespace App\Filament\Resources\Users\Schemas\Components;

use Filament\Forms\Components\TextInput;

class PasswordInput
{
    public static function make(): TextInput
    {
        return TextInput::make('password')
            ->label(__('Password'))
            ->password()
            ->required()
            ->maxLength(255)
            ->trim()
            ->hiddenOn('edit')
            ->autocomplete('new-password');
        // ->belowContent(__('Choose a temporary password. The user will be prompted to change it after the first login.'))
    }
}
