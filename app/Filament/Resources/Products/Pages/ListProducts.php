<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Traits\CanResetTable;
use App\Filament\Traits\HasCreateAction;
use App\Filament\Traits\HasOrderTableDefaults;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListProducts extends ListRecords
{
    use CanResetTable;
    use HasCreateAction;
    use HasOrderTableDefaults;
    use HasResizableColumn;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->size('sm'),
            ImportAction::make()
                ->importer(ProductImporter::class)
                ->label(__('filament-actions::import.modal.actions.import.label'))
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->size('sm'),
            ExportAction::make()
                ->exporter(ProductExporter::class)
                ->label(__('filament-actions::export.modal.actions.export.label'))
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->size('sm'),
        ];
    }
}
