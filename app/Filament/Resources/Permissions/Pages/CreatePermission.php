<?php

namespace App\Filament\Resources\Permissions\Pages;

use App\Filament\Resources\Permissions\PermissionsResource;
use App\Filament\Traits\HasCreateDefaults;
use Filament\Resources\Pages\CreateRecord;

class CreatePermission extends CreateRecord
{
    use HasCreateDefaults;

    protected static string $resource = PermissionsResource::class;
}
