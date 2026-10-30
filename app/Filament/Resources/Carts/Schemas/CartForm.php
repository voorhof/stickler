<?php

namespace App\Filament\Resources\Carts\Schemas;

use App\Filament\Schemas\Sections\ActivityHistorySection;
use App\Filament\Schemas\Sections\DatabaseInfoSection;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->columnSpanFull()
                    ->schema([
                        Section::make(__('Cart Details'))
                            ->icon(Heroicon::OutlinedShoppingBag)
                            ->collapsible()
                            ->schema([
                                Grid::make()->schema([
                                    Select::make('user_id')
                                        ->label(__('Customer'))
                                        ->relationship('user', 'name')
                                        ->searchable()
                                        ->nullable()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            $set('is_guest', ! $state);
                                        }),
                                    Toggle::make('is_guest')
                                        ->label(__('Guest cart'))
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            if ($state) {
                                                $set('user_id', null);
                                            }
                                        }),
                                    DatePicker::make('expires_at')
                                        ->label(__('Expires at'))
                                        ->nullable(),
                                ]),
                                TextInput::make('customer_data.name')
                                    ->label(__('Customer name'))
                                    ->nullable(),
                                TextInput::make('customer_data.email')
                                    ->label(__('Customer email'))
                                    ->email()
                                    ->nullable(),
                                TextInput::make('customer_data.phone')
                                    ->label(__('Customer phone'))
                                    ->tel()
                                    ->nullable(),
                                Section::make(__('Shipping address'))
                                    ->collapsible()
                                    ->schema([
                                        TextInput::make('customer_data.shipping_address.street')
                                            ->label(__('Street'))
                                            ->nullable(),
                                        Grid::make(3)->schema([
                                            TextInput::make('customer_data.shipping_address.city')
                                                ->label(__('City'))
                                                ->nullable(),
                                            TextInput::make('customer_data.shipping_address.postal_code')
                                                ->label(__('Postal code'))
                                                ->nullable(),
                                            TextInput::make('customer_data.shipping_address.country')
                                                ->label(__('Country'))
                                                ->nullable(),
                                        ]),
                                    ]),
                                Section::make(__('Billing address'))
                                    ->collapsible()
                                    ->schema([
                                        TextInput::make('customer_data.billing_address.street')
                                            ->label(__('Street'))
                                            ->nullable(),
                                        Grid::make(3)->schema([
                                            TextInput::make('customer_data.billing_address.city')
                                                ->label(__('City'))
                                                ->nullable(),
                                            TextInput::make('customer_data.billing_address.postal_code')
                                                ->label(__('Postal code'))
                                                ->nullable(),
                                            TextInput::make('customer_data.billing_address.country')
                                                ->label(__('Country'))
                                                ->nullable(),
                                        ]),
                                    ]),
                                Textarea::make('customer_data.notes')
                                    ->label(__('Notes'))
                                    ->rows(3)
                                    ->nullable(),
                            ]),
                        DatabaseInfoSection::make(),
                        ActivityHistorySection::make(),
                    ]),
            ]);
    }
}
