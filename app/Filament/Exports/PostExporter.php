<?php

namespace App\Filament\Exports;

use App\Models\Post;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

class PostExporter extends Exporter
{
    protected static ?string $model = Post::class;

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
        $body = 'Your post export has completed and '.Str::of('row')->counted($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Str::of('row')->counted($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
