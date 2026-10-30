<?php

namespace App\Filament\Resources\Carts\Schemas;

use App\Filament\Schemas\Sections\ActivityHistorySection;
use App\Filament\Schemas\Sections\DatabaseInfoSection;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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
                            ]),
                        DatabaseInfoSection::make(),
                        ActivityHistorySection::make(),
                    ]),
            ]);
    }
}
