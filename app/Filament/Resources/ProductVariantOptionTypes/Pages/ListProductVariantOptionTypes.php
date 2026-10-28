<?php

namespace App\Filament\Resources\ProductVariantOptionTypes\Pages;

use App\Filament\Resources\ProductVariantOptionTypes\ProductVariantOptionTypeResource;
use App\Filament\Traits\CanResetTable;
use App\Filament\Traits\HasCreateAction;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Resources\Pages\ListRecords;

class ListProductVariantOptionTypes extends ListRecords
{
    use CanResetTable;
    use HasCreateAction;
    use HasResizableColumn;

    protected static string $resource = ProductVariantOptionTypeResource::class;
}
