<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Exports\OrderExporter;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Traits\CanResetTable;
use App\Filament\Traits\HasCreateAction;
use App\Filament\Traits\HasOrderTableDefaults;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    use CanResetTable;
    use HasCreateAction;
    use HasOrderTableDefaults;
    use HasResizableColumn;

    protected static string $resource = OrderResource::class;

    protected function getTableQuery(): Builder
    {
        return static::getResource()::getEloquentQuery()->with(['user'])->withCount(['orderItems']);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->size('sm'),
            ExportAction::make()
                ->exporter(OrderExporter::class)
                ->label(__('filament-actions::export.modal.actions.export.label'))
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->size('sm'),
        ];
    }
}
