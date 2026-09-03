<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Traits\HasCreateDefaults;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    use HasCreateDefaults;

    protected static string $resource = ProjectResource::class;
}
