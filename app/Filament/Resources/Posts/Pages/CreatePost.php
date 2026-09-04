<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Filament\Traits\HasCreateDefaults;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    use HasCreateDefaults;

    protected static string $resource = PostResource::class;
}
