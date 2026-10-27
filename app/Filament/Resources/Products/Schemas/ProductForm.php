<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Filament\Resources\Products\Schemas\Sections\ProductInfoSection;
use App\Filament\Schemas\Sections\ActivityHistorySection;
use App\Filament\Schemas\Sections\ContentSection;
use App\Filament\Schemas\Sections\DatabaseInfoSection;
use App\Filament\Schemas\Sections\DocumentUploadSection;
use App\Filament\Schemas\Sections\ImageUploadSection;
use App\Filament\Schemas\Sections\SettingsSection;
use App\Filament\Schemas\Sections\TagsSection;
use App\Filament\Schemas\Sections\VideoUploadSection;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->columnSpanFull()
                    ->schema([
                        ContentSection::make(
                            hasWebsite: true,
                        ),
                        ProductInfoSection::make(),
                        ImageUploadSection::make(),
                        VideoUploadSection::make(),
                        DocumentUploadSection::make(),
                        TagsSection::make(),
                        SettingsSection::make(),
                        DatabaseInfoSection::make(),
                        ActivityHistorySection::make(),
                    ]),
            ]);
    }
}
