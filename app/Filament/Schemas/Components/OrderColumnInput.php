<?php

namespace App\Filament\Schemas\Components;

use Filament\Forms\Components\TextInput;

class OrderColumnInput
{
    public static function make(): TextInput
    {
        return TextInput::make('order_column')
            ->label(__('Order column'))
            ->numeric()
            ->hiddenOn('create')
            ->default(1)
            ->placeholder(1)
            ->minValue(1)
            ->extraInputAttributes([
                'min' => 1,
            ]);
    }
}
