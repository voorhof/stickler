<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Traits\HasCreateDefaults;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use HasCreateDefaults;

    protected static string $resource = ProductResource::class;
}
