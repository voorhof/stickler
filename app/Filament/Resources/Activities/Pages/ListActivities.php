<?php

namespace App\Filament\Resources\Activities\Pages;

use App\Filament\Actions\Spatie\ActivityLogCleanupAction;
use App\Filament\Resources\Activities\ActivityResource;
use App\Filament\Traits\CanResetTable;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Resources\Pages\ListRecords;

class ListActivities extends ListRecords
{
    use CanResetTable;
    use HasResizableColumn;

    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActivityLogCleanupAction::make(),
        ];
    }
}
