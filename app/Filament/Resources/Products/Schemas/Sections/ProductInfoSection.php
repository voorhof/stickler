<?php

namespace App\Filament\Resources\Products\Schemas\Sections;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class ProductInfoSection
{
    public static function make(): Section
    {
        return Section::make(__('Product Info'))
            ->icon(Heroicon::OutlinedShoppingBag)
            ->schema([
                Grid::make()->schema([
                    TextInput::make('sku')
                        ->label(__('SKU'))
                        ->maxLength(64)
                        ->trim()
                        ->placeholder('PRD-001')
                        ->belowContent(__('Unique stock keeping unit identifier.')),
                    TextInput::make('stock_quantity')
                        ->label(__('Stock Quantity'))
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->default(0),

                    TextInput::make('price')
                        ->label(__('Price'))
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->prefix('€')
                        ->step(0.01),
                    TextInput::make('discount_price')
                        ->label(__('Discount price'))
                        ->numeric()
                        ->minValue(0)
                        ->prefix('€')
                        ->step(0.01)
                        ->visible(fn ($get) => $get('is_discounted')),

                    Checkbox::make('is_discounted')
                        ->label(__('Is discounted'))
                        ->default(false),
                ]),
            ]);
    }
}
