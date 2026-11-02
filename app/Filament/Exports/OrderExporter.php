<?php

namespace App\Filament\Exports;

use App\Models\Order;
use BackedEnum;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class OrderExporter extends Exporter
{
    protected static ?string $model = Order::class;

    /**
     * Financial fields (subtotal, tax_amount, shipping_amount, total) automatically use the model's
     * Attribute accessors, so exported values will be in euros (e.g., 25.00), not cents.
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('order_number'),
            ExportColumn::make('user.name')
                ->label('Customer name'),
            ExportColumn::make('user.email')
                ->label('Customer email'),
            ExportColumn::make('status')
                ->formatStateUsing(fn (mixed $state): ?string => $state instanceof BackedEnum ? (string) $state->value : (is_scalar($state) ? (string) $state : null)),
            ExportColumn::make('payment_status')
                ->formatStateUsing(fn (mixed $state): ?string => $state instanceof BackedEnum ? (string) $state->value : (is_scalar($state) ? (string) $state : null)),
            ExportColumn::make('subtotal'),
            ExportColumn::make('tax_amount'),
            ExportColumn::make('shipping_amount'),
            ExportColumn::make('total'),
            ExportColumn::make('order_items_count')
                ->counts('orderItems')
                ->state(fn (Order $record): int => $record->order_items_count ?? $record->orderItems()->count())
                ->label('Order items count'),
            ExportColumn::make('payment_method'),
            ExportColumn::make('payment_provider'),
            ExportColumn::make('payment_provider_id'),
            ExportColumn::make('shipping_address')
                ->listAsJson(),
            ExportColumn::make('billing_address')
                ->listAsJson(),
            ExportColumn::make('notes'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('deleted_at'),
            ExportColumn::make('created_by_user_id'),
            ExportColumn::make('updated_by_user_id'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your order export has completed and '.Str::of('row')->counted($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Str::of('row')->counted($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
