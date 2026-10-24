<?php

namespace App\Filament\Resources\PageViews\Pages;

use App\Filament\Actions\PageViewCleanupAction;
use App\Filament\Resources\PageViews\PageViewResource;
use App\Filament\Traits\CanResetTable;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Resources\Pages\ListRecords;

class ListPageViews extends ListRecords
{
    use CanResetTable;
    use HasResizableColumn;

    protected static string $resource = PageViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PageViewCleanupAction::make(),
        ];
    }
}
