<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Traits\HasEditDefaults;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    use HasEditDefaults;

    protected static string $resource = UserResource::class;
}
