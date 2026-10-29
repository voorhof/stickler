<?php

namespace App\Filament\Resources\Carts\Pages;

use App\Filament\Resources\Carts\CartResource;
use App\Filament\Traits\HasCreateDefaults;
use Filament\Resources\Pages\CreateRecord;

class CreateCart extends CreateRecord
{
    use HasCreateDefaults;

    protected static string $resource = CartResource::class;
}
