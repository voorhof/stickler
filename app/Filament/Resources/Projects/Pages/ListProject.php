<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Exports\ProjectExporter;
use App\Filament\Imports\ProjectImporter;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Traits\CanResetTable;
use App\Filament\Traits\HasCreateAction;
use App\Filament\Traits\HasOrderTableDefaults;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListProject extends ListRecords
{
    use CanResetTable;
    use HasCreateAction;
    use HasOrderTableDefaults;
    use HasResizableColumn;

    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->size('sm'),
            ImportAction::make()
                ->importer(ProjectImporter::class)
                ->label(__('filament-actions::import.modal.actions.import.label'))
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->size('sm'),
            ExportAction::make()
                ->exporter(ProjectExporter::class)
                ->label(__('filament-actions::export.modal.actions.export.label'))
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->size('sm'),
        ];
    }
}
