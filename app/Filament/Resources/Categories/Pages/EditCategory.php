<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Traits\HasEditDefaults;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    use HasEditDefaults;

    protected static string $resource = CategoryResource::class;
}
