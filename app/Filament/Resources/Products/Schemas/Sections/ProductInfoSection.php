<?php

namespace App\Filament\Resources\Products\Schemas\Sections;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
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
                        ->default(0)
                        ->belowContent(__('The number of items available in stock.')),
                    TextInput::make('price')
                        ->label(__('Price'))
                        ->required()
                        ->numeric()
                        ->minValue(fn (Get $get): int => $get('discount_price') ?? 0)
                        ->prefix('€')
                        ->step(0.01),
                    TextInput::make('discount_price')
                        ->label(__('Discount price'))
                        ->required(fn (Get $get): bool => $get('is_discounted'))
                        ->visible(fn (Get $get): bool => $get('is_discounted'))
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(fn (Get $get): int => $get('price') ?? 0)
                        ->prefix('€')
                        ->step(0.01),
                    Toggle::make('is_discounted')
                        ->label(__('Is discounted'))
                        ->live()
                        ->belowContent(__('Discounted price is now valid.'))
                        ->columnSpanFull(),
                ]),
            ]);
    }
}
