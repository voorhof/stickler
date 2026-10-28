<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Schemas\Sections\ActivityHistorySection;
use App\Filament\Schemas\Sections\DatabaseInfoSection;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->columnSpanFull()
                    ->schema([
                        Section::make(__('Order Details'))
                            ->icon(Heroicon::OutlinedShoppingCart)
                            ->collapsible()
                            ->schema([
                                Grid::make()->schema([
                                    TextInput::make('order_number')
                                        ->label(__('Order number'))
                                        ->required()
                                        ->disabled()
                                        ->visibleOn('edit'),
                                    Select::make('user_id')
                                        ->label(__('Customer'))
                                        ->relationship('user', 'name')
                                        ->searchable()
                                        ->nullable(),
                                    Select::make('status')
                                        ->label(__('Status'))
                                        ->options(OrderStatus::class)
                                        ->required(),
                                    Select::make('payment_status')
                                        ->label(__('Payment status'))
                                        ->options(PaymentStatus::class)
                                        ->required(),
                                    TextInput::make('payment_method')
                                        ->label(__('Payment method'))
                                        ->nullable(),
                                ]),
                            ]),
                        Section::make(__('Prices'))
                            ->icon(Heroicon::OutlinedBanknotes)
                            ->collapsible()
                            ->schema([
                                Grid::make()->schema([
                                    TextInput::make('subtotal')
                                        ->label(__('Subtotal'))
                                        ->numeric()
                                        ->prefix('€')
                                        ->required(),
                                    TextInput::make('tax_amount')
                                        ->label(__('Tax amount'))
                                        ->numeric()
                                        ->prefix('€')
                                        ->required(),
                                    TextInput::make('shipping_amount')
                                        ->label(__('Shipping amount'))
                                        ->numeric()
                                        ->prefix('€')
                                        ->required(),
                                    TextInput::make('total')
                                        ->label(__('Total'))
                                        ->numeric()
                                        ->prefix('€')
                                        ->required()
                                        ->disabled(),
                                ]),
                            ]),
                        Section::make(__('Shipping Address'))
                            ->icon(Heroicon::OutlinedMapPin)
                            ->collapsible()
                            ->schema([
                                TextInput::make('shipping_address.street')
                                    ->label(__('Street'))
                                    ->nullable(),
                                Grid::make(3)->schema([
                                    TextInput::make('shipping_address.city')
                                        ->label(__('City'))
                                        ->nullable(),
                                    TextInput::make('shipping_address.postal_code')
                                        ->label(__('Postal code'))
                                        ->nullable(),
                                    TextInput::make('shipping_address.country')
                                        ->label(__('Country'))
                                        ->nullable(),
                                ]),
                            ]),
                        Section::make(__('Billing Address'))
                            ->icon(Heroicon::OutlinedCreditCard)
                            ->collapsible()
                            ->schema([
                                TextInput::make('billing_address.street')
                                    ->label(__('Street'))
                                    ->nullable(),
                                Grid::make(3)->schema([
                                    TextInput::make('billing_address.city')
                                        ->label(__('City'))
                                        ->nullable(),
                                    TextInput::make('billing_address.postal_code')
                                        ->label(__('Postal code'))
                                        ->nullable(),
                                    TextInput::make('billing_address.country')
                                        ->label(__('Country'))
                                        ->nullable(),
                                ]),
                            ]),
                        Section::make(__('Order Items'))
                            ->icon(Heroicon::OutlinedListBullet)
                            ->collapsible()
                            ->schema([
                                Repeater::make('items')
                                    ->relationship()
                                    ->label(__('Order items'))
                                    ->schema([
                                        Grid::make()->schema([
                                            TextInput::make('product_name')
                                                ->label(__('Product'))
                                                ->required(),
                                            TextInput::make('product_variant_label')
                                                ->label(__('Variant'))
                                                ->nullable(),
                                            TextInput::make('sku')
                                                ->label(__('SKU'))
                                                ->nullable(),
                                            TextInput::make('quantity')
                                                ->label(__('Quantity'))
                                                ->numeric()
                                                ->minValue(1)
                                                ->required(),
                                            TextInput::make('unit_price')
                                                ->label(__('Unit price'))
                                                ->numeric()
                                                ->prefix('€')
                                                ->required(),
                                            TextInput::make('total_price')
                                                ->label(__('Total price'))
                                                ->numeric()
                                                ->prefix('€')
                                                ->required()
                                                ->disabled()
                                                ->dehydrated(false),
                                        ]),
                                    ])
                                    ->defaultItems(0)
                                    ->columns(1)
                                    ->reorderable()
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['product_name'] ?? null),
                            ]),
                        Section::make(__('Notes'))
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->collapsible()
                            ->schema([
                                Textarea::make('notes')
                                    ->label(__('Notes'))
                                    ->nullable()
                                    ->rows(3),
                            ]),
                        DatabaseInfoSection::make(),
                        ActivityHistorySection::make(),
                    ]),
            ]);
    }
}
