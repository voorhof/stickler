<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Resources\Users\Schemas\Sections\DetailsSection;
use App\Filament\Resources\Users\Schemas\Sections\RoleSection;
use App\Filament\Schemas\Sections\ActivityHistorySection;
use App\Filament\Schemas\Sections\DatabaseInfoSection;
use App\Filament\Schemas\Sections\SettingsSection;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->columnSpanFull()
                    ->schema([
                        DetailsSection::make(),
                        RoleSection::make(),
                        SettingsSection::make(
                            hasPublished: false,
                        ),
                        DatabaseInfoSection::make(),
                        ActivityHistorySection::make(),
                    ]),
            ]);
    }
}
