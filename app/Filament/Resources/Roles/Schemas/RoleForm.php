<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Filament\Resources\Roles\Schemas\Sections\DetailsSection;
use App\Filament\Resources\Roles\Schemas\Sections\PermissionsSection;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Schemas\Sections\ActivityHistorySection;
use App\Filament\Schemas\Sections\DatabaseInfoSection;
use App\Filament\Schemas\Sections\LinkedModelsSection;
use App\Filament\Schemas\Sections\SettingsSection;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->columnSpanFull()
                    ->schema([
                        DetailsSection::make(),
                        PermissionsSection::make(),
                        LinkedModelsSection::make(
                            resource: UserResource::class,
                            relationName: 'users',
                            modelLabel: 'Users',
                            icon: Heroicon::OutlinedUsers,
                            entryColumn: 'name',
                        ),

                        SettingsSection::make(
                            hasPublished: false,
                            hasSlug: false,
                        ),
                        DatabaseInfoSection::make(),
                        ActivityHistorySection::make(),
                    ]),
            ]);
    }
}
