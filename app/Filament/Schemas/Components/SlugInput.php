<?php

namespace App\Filament\Schemas\Components;

use Filament\Forms\Components\TextInput;

class SlugInput
{
    public static function make(string $name = 'slug'): TextInput
    {
        return TextInput::make($name)
            ->label(__('Slug'))
            ->required()
            ->unique()
            ->minLength(2)
            ->maxLength(48)
            ->regex('/^[a-z0-9-]+$/')
            ->extraInputAttributes([
                'minLength' => 2,
                'maxlength' => 48,
                'pattern' => '[a-z0-9\-]+',
                'title' => __('The slug must consist solely of lower-case letters, numbers and hyphens.'),
            ])
            ->belowContent(__('The slug must consist solely of lower-case letters, numbers and hyphens.'));
    }
}
