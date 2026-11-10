<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Users\Schemas\Sections\DetailsSection;
use App\Filament\Resources\Users\Schemas\Sections\RoleSection;
use App\Filament\Schemas\Sections\ActivityHistorySection;
use App\Filament\Schemas\Sections\DatabaseInfoSection;
use App\Filament\Schemas\Sections\LinkedModelsSection;
use App\Filament\Schemas\Sections\SettingsSection;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

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
                        LinkedModelsSection::make(
                            resource: OrderResource::class,
                            relationName: 'orders',
                            modelLabel: 'Orders',
                            icon: Heroicon::OutlinedShoppingCart),
                        DatabaseInfoSection::make(),
                        ActivityHistorySection::make(),
                    ]),
            ]);
    }
}
