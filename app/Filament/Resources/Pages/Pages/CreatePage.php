<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Filament\Traits\HasCreateDefaults;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    use HasCreateDefaults;

    protected static string $resource = PageResource::class;
}
