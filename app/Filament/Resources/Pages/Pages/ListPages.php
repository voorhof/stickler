<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Exports\PageExporter;
use App\Filament\Imports\PageImporter;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Traits\CanResetTable;
use App\Filament\Traits\HasCreateAction;
use App\Filament\Traits\HasOrderTableDefaults;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListPages extends ListRecords
{
    use CanResetTable;
    use HasCreateAction;
    use HasOrderTableDefaults;
    use HasResizableColumn;

    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->size('sm'),
            ImportAction::make()
                ->importer(PageImporter::class)
                ->label(__('filament-actions::import.modal.actions.import.label'))
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->size('sm'),
            ExportAction::make()
                ->exporter(PageExporter::class)
                ->label(__('filament-actions::export.modal.actions.export.label'))
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->size('sm'),
        ];
    }
}
