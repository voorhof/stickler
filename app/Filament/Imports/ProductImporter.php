<?php

namespace App\Filament\Imports;

use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    /**
     * The price and discount_price fields automatically use the model's Attribute accessor/mutator,
     * so imported/exported values will be in euros (e.g., 4.00), not cents.
     */
    public static function getColumns(): array
    {
        return [
            ImportColumn::make('slug')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('title')
                ->requiredMapping()
                ->rules(['required', 'max:128']),
            ImportColumn::make('intro')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('content'),
            ImportColumn::make('sku')
                ->rules(['nullable', 'max:64']),
            ImportColumn::make('price')
                ->rules(['required', 'numeric', 'min:0']),
            ImportColumn::make('discount_price')
                ->rules(['nullable', 'numeric', 'min:0']),
            ImportColumn::make('is_discounted')
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('stock_quantity')
                ->rules(['required', 'integer', 'min:0']),
            ImportColumn::make('website_title')
                ->rules(['nullable']),
            ImportColumn::make('website_url')
                ->rules(['nullable', 'url']),
            ImportColumn::make('order_column')
                ->rules(['nullable', 'integer']),
            ImportColumn::make('published_at')
                ->rules(['nullable', 'date']),
        ];
    }

    public function resolveRecord(): Product
    {
        $keyName = app(static::getModel())->getKeyName();
        $keyColumnName = $this->columnMap[$keyName] ?? $keyName;

        $record = isset($this->data[$keyColumnName])
            ? static::getModel()::find($this->data[$keyColumnName])
            : null;

        $product = $record ?? new Product;

        $userId = $this->import?->user_id ?? Auth::id();

        if ($userId) {
            if (! $product->exists) {
                $product->created_by_user_id = $userId;
            }
            $product->updated_by_user_id = $userId;
        }

        return $product;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
