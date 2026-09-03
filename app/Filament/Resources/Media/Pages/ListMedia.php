<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Actions\Spatie\MediaLibraryRegenerateAction;
use App\Filament\Resources\Media\MediaResource;
use App\Filament\Traits\CanResetTable;
use App\Filament\Traits\HasOrderTableDefaults;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Resources\Pages\ListRecords;

class ListMedia extends ListRecords
{
    use CanResetTable;
    use HasOrderTableDefaults;
    use HasResizableColumn;

    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            MediaLibraryRegenerateAction::make(),
        ];
    }
}
