<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Schemas\Sections\ActivityHistorySection;
use App\Filament\Schemas\Sections\DatabaseInfoSection;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                        Section::make(__('Order Items'))
                            ->icon(Heroicon::OutlinedListBullet)
                            ->collapsible()
                            ->schema([
                                Repeater::make('orderItems')
                                    ->relationship()
                                    ->label(__('Order items'))
                                    ->live()
                                    ->schema([
                                        Grid::make()->schema([
                                            Select::make('product_id')
                                                ->label(__('Product'))
                                                ->relationship('product', 'title')
                                                ->searchable()
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                                    $product = Product::find($state);
                                                    if ($product) {
                                                        $set('product_name', $product->title);
                                                        $set('unit_price', $product->price);
                                                        $set('sku', $product->sku);
                                                        $quantity = $get('quantity');
                                                        if ($quantity) {
                                                            $set('total_price', round($product->price * $quantity, 2));
                                                        }
                                                    }
                                                    $set('product_variant_id', null);
                                                    $set('product_variant_label', null);
                                                    self::recalculatePricesFromItem($get, $set);
                                                }),
                                            Select::make('product_variant_id')
                                                ->label(__('Variant'))
                                                ->live()
                                                ->options(function (callable $get): array {
                                                    $productId = $get('product_id');
                                                    if (! $productId) {
                                                        return [];
                                                    }

                                                    return ProductVariant::where('product_id', $productId)
                                                        ->with('options')
                                                        ->get()
                                                        ->mapWithKeys(fn (ProductVariant $variant) => [$variant->id => $variant->label])
                                                        ->all();
                                                })
                                                ->searchable()
                                                ->nullable()
                                                ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                                    $variant = ProductVariant::with('options')->find($state);
                                                    if ($variant) {
                                                        $set('product_variant_label', $variant->label);
                                                        if ($variant->price !== null) {
                                                            $set('unit_price', $variant->price);
                                                        } else {
                                                            $product = Product::find($get('product_id'));
                                                            if ($product) {
                                                                $set('unit_price', $product->price);
                                                            }
                                                        }
                                                        if ($variant->sku !== null) {
                                                            $set('sku', $variant->sku);
                                                        }
                                                    } else {
                                                        $product = Product::find($get('product_id'));
                                                        if ($product) {
                                                            $set('unit_price', $product->price);
                                                            $set('sku', $product->sku);
                                                        }
                                                    }
                                                    $unitPrice = $get('unit_price');
                                                    $quantity = $get('quantity');
                                                    if ($unitPrice && $quantity) {
                                                        $set('total_price', round($unitPrice * $quantity, 2));
                                                    }
                                                    self::recalculatePricesFromItem($get, $set);
                                                }),
                                            TextInput::make('product_name')
                                                ->label(__('Product name'))
                                                ->disabled()
                                                ->dehydrated(),
                                            TextInput::make('product_variant_label')
                                                ->label(__('Variant label'))
                                                ->disabled()
                                                ->dehydrated(),
                                            TextInput::make('sku')
                                                ->label(__('SKU'))
                                                ->disabled()
                                                ->dehydrated(),
                                            TextInput::make('quantity')
                                                ->label(__('Quantity'))
                                                ->numeric()
                                                ->minValue(1)
                                                ->default(1)
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(function (callable $get, callable $set) {
                                                    $unitPrice = $get('unit_price');
                                                    $quantity = $get('quantity');
                                                    if ($unitPrice && $quantity) {
                                                        $set('total_price', round($unitPrice * $quantity, 2));
                                                    }
                                                    self::recalculatePricesFromItem($get, $set);
                                                }),
                                            TextInput::make('unit_price')
                                                ->label(__('Unit price'))
                                                ->numeric()
                                                ->prefix('€')
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(function (callable $get, callable $set) {
                                                    $unitPrice = $get('unit_price');
                                                    $quantity = $get('quantity');
                                                    if ($unitPrice && $quantity) {
                                                        $set('total_price', round($unitPrice * $quantity, 2));
                                                    }
                                                    self::recalculatePricesFromItem($get, $set);
                                                }),
                                            TextInput::make('total_price')
                                                ->label(__('Total price'))
                                                ->numeric()
                                                ->prefix('€')
                                                ->readOnly(),
                                        ]),
                                    ])
                                    ->defaultItems(0)
                                    ->columns(1)
                                    ->reorderable()
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['product_name'] ?? null)
                                    ->deleteAction(fn (Action $action) => $action->requiresConfirmation())
                                    ->afterStateHydrated(function (Get $get, Set $set) {
                                        self::recalculatePrices($get, $set);
                                    })
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::recalculatePrices($get, $set);
                                    }),
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
                                        ->required()
                                        ->live()
                                        ->readOnly(),
                                    TextInput::make('tax_amount')
                                        ->label(__('Tax amount'))
                                        ->numeric()
                                        ->prefix('€')
                                        ->required()
                                        ->live()
                                        ->readOnly(),
                                    TextInput::make('shipping_amount')
                                        ->label(__('Shipping amount'))
                                        ->numeric()
                                        ->prefix('€')
                                        ->default(0)
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            self::recalculatePrices($get, $set);
                                        }),
                                    TextInput::make('total')
                                        ->label(__('Total'))
                                        ->numeric()
                                        ->prefix('€')
                                        ->required()
                                        ->live()
                                        ->readOnly(),
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

    /*
    |--------------------------------------------------------------------------
    * Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Recalculate the order prices based on order items.
     *
     * Subtotal = sum of all item total prices
     * Tax = 21% of subtotal
     * Total = subtotal + tax + shipping
     */
    protected static function recalculatePrices(Get $get, Set $set): void
    {
        $items = $get('orderItems') ?? [];
        $subtotal = collect($items)->sum(fn (array $item) => ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0));
        $tax = round($subtotal * 0.21, 2);
        $shipping = $get('shipping_amount') ?? 0;
        $total = $subtotal + $tax + $shipping;

        $set('subtotal', round($subtotal, 2));
        $set('tax_amount', round($tax, 2));
        $set('total', round($total, 2));
    }

    /**
     * Recalculate the order prices from within a repeater item callback.
     *
     * Uses path-traversal to set parent form fields directly,
     * bypassing the reactive cycle limitations.
     */
    protected static function recalculatePricesFromItem(callable $get, callable $set): void
    {
        $items = $get('../../orderItems') ?? [];
        $subtotal = collect($items)->sum(fn (array $item) => ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0));
        $tax = round($subtotal * 0.21, 2);
        $shipping = $get('../../shipping_amount') ?? 0;
        $total = $subtotal + $tax + $shipping;

        $set('../../subtotal', round($subtotal, 2));
        $set('../../tax_amount', round($tax, 2));
        $set('../../total', round($total, 2));
    }
}
