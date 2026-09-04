<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Traits\HasEditDefaults;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    use HasEditDefaults;

    protected static string $resource = RoleResource::class;
}
