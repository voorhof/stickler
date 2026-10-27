<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    /**
     * The price and discount_price fields automatically use the model's Attribute accessor/mutator,
     * so imported/exported values will be in euros (e.g., 4.00), not cents.
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('slug'),
            ExportColumn::make('title'),
            ExportColumn::make('intro'),
            ExportColumn::make('content')
                ->formatStateUsing(fn (mixed $state): ?string => $state instanceof Htmlable ? $state->toHtml() : $state),
            ExportColumn::make('sku'),
            ExportColumn::make('price'),
            ExportColumn::make('discount_price'),
            ExportColumn::make('is_discounted'),
            ExportColumn::make('stock_quantity'),
            ExportColumn::make('website_title'),
            ExportColumn::make('website_url'),
            ExportColumn::make('order_column'),
            ExportColumn::make('published_at'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('deleted_at'),
            ExportColumn::make('created_by_user_id'),
            ExportColumn::make('updated_by_user_id'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your product export has completed and '.Str::of('row')->counted($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Str::of('row')->counted($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
