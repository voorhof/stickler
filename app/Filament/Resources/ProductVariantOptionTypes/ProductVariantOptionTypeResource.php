<?php

namespace App\Filament\Resources\ProductVariantOptionTypes;

use App\Filament\Resources\ProductVariantOptionTypes\Pages\CreateProductVariantOptionType;
use App\Filament\Resources\ProductVariantOptionTypes\Pages\EditProductVariantOptionType;
use App\Filament\Resources\ProductVariantOptionTypes\Pages\ListProductVariantOptionTypes;
use App\Filament\Resources\ProductVariantOptionTypes\Schemas\ProductVariantOptionTypeForm;
use App\Filament\Resources\ProductVariantOptionTypes\Tables\ProductVariantOptionTypesTable;
use App\Models\ProductVariantOptionType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductVariantOptionTypeResource extends Resource
{
    protected static ?string $model = ProductVariantOptionType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::RectangleStack;

    protected static ?int $navigationSort = 2200;

    public static function getNavigationGroup(): ?string
    {
        return __('Shop management');
    }

    public static function getModelLabel(): string
    {
        return __('Variant Option Type');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Variant Options');
    }

    public static function form(Schema $schema): Schema
    {
        return ProductVariantOptionTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductVariantOptionTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            Relations\ProductVariantOptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductVariantOptionTypes::route('/'),
            'create' => CreateProductVariantOptionType::route('/create'),
            'edit' => EditProductVariantOptionType::route('/{record}/edit'),
        ];
    }
}
