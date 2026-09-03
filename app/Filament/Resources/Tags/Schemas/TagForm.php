<?php

namespace App\Filament\Resources\Tags\Schemas;

use App\Filament\Resources\Media\MediaResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Schemas\Sections\ActivityHistorySection;
use App\Filament\Schemas\Sections\ContentSection;
use App\Filament\Schemas\Sections\DatabaseInfoSection;
use App\Filament\Schemas\Sections\LinkedModelsSection;
use App\Filament\Schemas\Sections\SettingsSection;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->columnSpanFull()
                    ->schema([
                        ContentSection::make(
                            hasTitle: false,
                            hasName: true,
                            hasIntro: false,
                            hasContent: false,
                            hasCover: false,
                        ),
                        SettingsSection::make(
                            hasPublished: false,
                            slugName: 'url_slug',
                        ),
                        LinkedModelsSection::make(
                            resource: PostResource::class,
                            relationName: 'posts',
                            modelLabel: 'Blogposts',
                            icon: Heroicon::OutlinedPencilSquare,
                        ),
                        LinkedModelsSection::make(
                            resource: ProjectResource::class,
                            relationName: 'projects',
                            modelLabel: 'Projects',
                            icon: Heroicon::OutlinedBriefcase,
                        ),
                        LinkedModelsSection::make(
                            resource: MediaResource::class,
                            relationName: 'media',
                            modelLabel: 'Media',
                            icon: Heroicon::OutlinedCamera,
                            entryColumn: 'name',
                            grid: 3,
                        ),
                        DatabaseInfoSection::make(),
                        ActivityHistorySection::make(),
                    ]),
            ]);
    }
}
