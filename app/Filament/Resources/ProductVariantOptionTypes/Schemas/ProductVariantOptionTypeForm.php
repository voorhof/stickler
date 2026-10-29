<?php

namespace App\Filament\Resources\ProductVariantOptionTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductVariantOptionTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255)
                    ->placeholder(__('Type')),
            ]);
    }
}
