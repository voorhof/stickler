<?php

namespace App\Filament\Resources\Permissions\Pages;

use App\Filament\Resources\Permissions\PermissionsResource;
use App\Filament\Traits\HasEditDefaults;
use Filament\Resources\Pages\EditRecord;

class EditPermission extends EditRecord
{
    use HasEditDefaults;

    protected static string $resource = PermissionsResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
