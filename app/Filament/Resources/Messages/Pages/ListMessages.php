<?php

namespace App\Filament\Resources\Messages\Pages;

use App\Filament\Resources\Messages\MessageResource;
use App\Filament\Traits\CanResetTable;
use App\Filament\Traits\HasOrderTableDefaults;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Resources\Pages\ListRecords;

class ListMessages extends ListRecords
{
    use CanResetTable;
    use HasOrderTableDefaults;
    use HasResizableColumn;

    protected static string $resource = MessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
