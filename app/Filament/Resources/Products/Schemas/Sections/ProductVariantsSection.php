<?php

namespace App\Filament\Resources\Products\Schemas\Sections;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

class ProductVariantsSection
{
    public static function make(): Section
    {
        return Section::make(__('Product Variants'))
            ->description(__('Manage product variants like colors and sizes.'))
            ->icon(Heroicon::OutlinedSwatch)
            ->collapsible()
            ->schema([
                Repeater::make('variants')
                    ->relationship()
                    ->label(__('Variants'))
                    ->schema([
                        Grid::make()->schema([
                            TextInput::make('sku')
                                ->label(__('SKU'))
                                ->maxLength(64)
                                ->trim()
                                ->placeholder('PRD-001-RED'),
                            TextInput::make('stock_quantity')
                                ->label(__('Stock Quantity'))
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
                            TextInput::make('price')
                                ->label(__('Price'))
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(fn (Get $get): ?float => $get('../../price'))
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
                                ->columnSpanFull(),
                            Select::make('options')
                                ->label(__('Variant Options'))
                                ->multiple()
                                ->relationship('options', 'name')
                                ->searchable()
                                ->columnSpanFull(),
                        ]),
                    ])
                    ->columns(1)
                    ->reorderable()
                    ->addable()
                    ->deletable()
                    ->collapsible(),
            ]);
    }
}
