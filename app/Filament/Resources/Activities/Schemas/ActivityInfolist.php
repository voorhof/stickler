<?php

namespace App\Filament\Resources\Activities\Schemas;

use App\Filament\Resources\Activities\Schemas\Sections\AttributesSection;
use App\Filament\Resources\Activities\Schemas\Sections\InfoSection;
use App\Filament\Resources\Activities\Schemas\Sections\PropertiesSection;
use App\Filament\Schemas\Sections\ActivityHistorySection;
use App\Filament\Schemas\Sections\DatabaseInfoSection;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ActivityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->schema([
                        InfoSection::make(),
                        AttributesSection::make(),
                        PropertiesSection::make(),
                        DatabaseInfoSection::make(),
                        ActivityHistorySection::make(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
