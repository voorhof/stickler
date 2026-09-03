<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Resources\Tags\TagResource;
use App\Filament\Traits\HasEditDefaults;
use Filament\Resources\Pages\EditRecord;

class EditTag extends EditRecord
{
    use HasEditDefaults;

    protected static string $resource = TagResource::class;
}
