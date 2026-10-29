<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Filament\Resources\Categories\Schemas\Sections\ForModelToggleSection;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Schemas\Sections\ActivityHistorySection;
use App\Filament\Schemas\Sections\ContentSection;
use App\Filament\Schemas\Sections\DatabaseInfoSection;
use App\Filament\Schemas\Sections\LinkedModelsSection;
use App\Filament\Schemas\Sections\SettingsSection;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->columnSpanFull()
                    ->schema([
                        ContentSection::make(
                            titleMax: 32,
                            hasIntro: false,
                            hasContent: false,
                            hasCover: false,
                        ),
                        ForModelToggleSection::make(),
                        LinkedModelsSection::make(
                            resource: PageResource::class,
                            relationName: 'pages',
                            modelLabel: 'Pages',
                            icon: Heroicon::OutlinedDocumentText),
                        LinkedModelsSection::make(
                            resource: PostResource::class,
                            relationName: 'posts',
                            modelLabel: 'Blogposts',
                            icon: Heroicon::OutlinedPencilSquare),
                        LinkedModelsSection::make(
                            resource: ProductResource::class,
                            relationName: 'products',
                            modelLabel: 'Products',
                            icon: Heroicon::OutlinedShoppingBag),
                        LinkedModelsSection::make(
                            resource: ProjectResource::class,
                            relationName: 'projects',
                            modelLabel: 'Projects',
                            icon: Heroicon::OutlinedBriefcase),
                        SettingsSection::make(
                            hasPublished: false,
                        ),
                        DatabaseInfoSection::make(),
                        ActivityHistorySection::make(),
                    ]),
            ]);
    }
}
