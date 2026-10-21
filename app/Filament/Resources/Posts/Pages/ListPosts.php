<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Exports\PostExporter;
use App\Filament\Imports\PostImporter;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Traits\CanResetTable;
use App\Filament\Traits\HasCreateAction;
use App\Filament\Traits\HasOrderTableDefaults;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListPosts extends ListRecords
{
    use CanResetTable;
    use HasCreateAction;
    use HasOrderTableDefaults;
    use HasResizableColumn;

    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->size('sm'),
            ImportAction::make()
                ->importer(PostImporter::class)
                ->label(__('filament-actions::import.modal.actions.import.label'))
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->size('sm'),
            ExportAction::make()
                ->exporter(PostExporter::class)
                ->label(__('filament-actions::export.modal.actions.export.label'))
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->size('sm'),
        ];
    }
}
