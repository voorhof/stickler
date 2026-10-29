<?php

namespace App\Filament\Resources\Carts\Relations;

use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CartItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Items');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label(__('Product'))
                    ->relationship('product', 'title')
                    ->searchable()
                    ->required(),
                Select::make('product_variant_id')
                    ->label(__('Variant'))
                    ->options(function ($get): array {
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
                    ->nullable(),
                TextInput::make('quantity')
                    ->label(__('Quantity'))
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                TextColumn::make('product_name')
                    ->label(__('Product'))
                    ->searchable(),
                TextColumn::make('product_variant_label')
                    ->label(__('Variant')),
                TextColumn::make('sku')
                    ->label(__('SKU'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('quantity')
                    ->label(__('Quantity'))
                    ->badge(),
                TextColumn::make('unit_price')
                    ->label(__('Unit price'))
                    ->money('EUR'),
                TextColumn::make('total_price')
                    ->label(__('Total price'))
                    ->money('EUR'),
            ])
            ->filters([])
            ->headerActions([
                CreateAction::make()
                    ->label(__('Add item'))
                    ->icon(Heroicon::OutlinedPlus)
                    ->mutateDataUsing(function (array $data): array {
                        $product = Product::find($data['product_id'] ?? null);

                        if ($product) {
                            $data['product_name'] = $product->title;
                            $data['sku'] = $product->sku;
                            $data['unit_price'] = $product->price;
                        }

                        $variant = ProductVariant::with('options')->find($data['product_variant_id'] ?? null);

                        if ($variant) {
                            $data['product_variant_label'] = $variant->label;
                            if ($variant->price !== null) {
                                $data['unit_price'] = $variant->price;
                            }
                            if ($variant->sku !== null) {
                                $data['sku'] = $variant->sku;
                            }
                        } else {
                            $data['product_variant_label'] = null;
                        }

                        $data['total_price'] = ($data['unit_price'] ?? 0) * ($data['quantity'] ?? 1);

                        return $data;
                    }),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->icon(Heroicon::OutlinedTrash),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
