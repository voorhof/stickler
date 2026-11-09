<?php

use App\Filament\Resources\Media\MediaResource;
use App\Filament\Schemas\Sections\LinkedModelsSection;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

describe('section creation', function () {
    it('creates linked models section', function () {
        $section = LinkedModelsSection::make(
            MediaResource::class,
            'media',
            'Media',
            Heroicon::OutlinedPhoto,
        );
        expect($section)->toBeInstanceOf(Section::class);
    });

    it('uses a unique key for each linked relation', function () {
        $pagesSection = LinkedModelsSection::make(
            MediaResource::class,
            'pages',
            'Pages',
            Heroicon::OutlinedPhoto,
        );
        $postsSection = LinkedModelsSection::make(
            MediaResource::class,
            'posts',
            'Posts',
            Heroicon::OutlinedPhoto,
        );

        expect($pagesSection->getKey(isAbsolute: false))->toBe('linked-pages-section')
            ->and($postsSection->getKey(isAbsolute: false))->toBe('linked-posts-section')
            ->and($pagesSection->getKey(isAbsolute: false))->not->toBe($postsSection->getKey(isAbsolute: false));
    });
});
