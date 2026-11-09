<?php

namespace App\Filament\Schemas\Components;

use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\Operation;

class OrderColumnInput
{
    public static function make(): TextInput
    {
        return TextInput::make('order_column')
            ->label(__('Order column'))
            ->numeric()
            ->hiddenOn(Operation::Create)
            ->default(1)
            ->placeholder(1)
            ->minValue(1)
            ->extraInputAttributes([
                'min' => 1,
            ]);
    }
}
