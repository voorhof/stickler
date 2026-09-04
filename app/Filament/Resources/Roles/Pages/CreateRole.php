<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Traits\HasCreateDefaults;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    use HasCreateDefaults;

    protected static string $resource = RoleResource::class;
}
