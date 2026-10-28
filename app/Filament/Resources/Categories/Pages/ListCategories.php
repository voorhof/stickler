<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Traits\CanResetTable;
use App\Filament\Traits\HasCreateAction;
use App\Filament\Traits\HasOrderTableDefaults;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    use CanResetTable;
    use HasCreateAction;
    use HasOrderTableDefaults;
    use HasResizableColumn;

    protected static string $resource = CategoryResource::class;
}
