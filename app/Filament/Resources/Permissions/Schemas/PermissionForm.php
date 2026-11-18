<?php

namespace App\Filament\Resources\Permissions\Schemas;

use App\Filament\Resources\Permissions\Schemas\Sections\DetailsSection;
use App\Filament\Schemas\Sections\ActivityHistorySection;
use App\Filament\Schemas\Sections\DatabaseInfoSection;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->columnSpanFull()
                    ->schema([
                        DetailsSection::make(),
                        DatabaseInfoSection::make(),
                        ActivityHistorySection::make(),
                    ]),
            ]);
    }
}
