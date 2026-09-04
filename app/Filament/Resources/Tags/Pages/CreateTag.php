<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Resources\Tags\TagResource;
use App\Filament\Traits\HasCreateDefaults;
use Filament\Resources\Pages\CreateRecord;

class CreateTag extends CreateRecord
{
    use HasCreateDefaults;

    protected static string $resource = TagResource::class;
}
