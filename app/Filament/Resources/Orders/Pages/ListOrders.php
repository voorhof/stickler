<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Traits\CanResetTable;
use App\Filament\Traits\HasCreateAction;
use App\Filament\Traits\HasOrderTableDefaults;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    use CanResetTable;
    use HasCreateAction;
    use HasOrderTableDefaults;
    use HasResizableColumn;

    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->size('sm'),
        ];
    }
}
