<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    User::factory()->create();
});

describe('basic rendering', function () {
    test('it renders standard rich content without images', function () {
        $page = Page::factory()->create([
            'content' => '<p>Hello <strong>World</strong></p>',
        ]);

        $rendered = $page->renderResponsiveRichContent();

        expect($rendered)->toContain('<p>Hello <strong>World</strong></p>');
    });

    test('it can render custom attribute names', function () {
        $page = Page::factory()->create([
            'intro' => '<p>Intro paragraph</p>',
        ]);

        $rendered = $page->renderResponsiveRichContent('intro');

        expect($rendered)->toContain('<p>Intro paragraph</p>');
    });
});

describe('responsive images', function () {
    test('it renders responsive image when media item has responsive images', function () {
        $page = Page::factory()->create();

        $file = UploadedFile::fake()->image('test-image.jpg', 1200, 800);
        $media = $page->addMedia($file)
            ->withResponsiveImages()
            ->toMediaCollection('content');

        $html = sprintf(
            '<p>Test text</p><img data-id="%s" alt="Custom Alt" title="Custom Title" loading="eager" />',
            $media->uuid,
        );

        $page->update(['content' => $html]);

        $rendered = $page->renderResponsiveRichContent();

        expect($rendered)
            ->toContain('class="img-fluid"')
            ->toContain('alt="Custom Alt"')
            ->toContain('title="Custom Title"')
            ->toContain('loading="eager"')
            ->toContain('sizes="(max-width: 768px) 100vw, 1200px"')
            ->toContain('srcset=')
            ->toContain($media->getUrl('webp'));
    });

    test('it renders non-responsive image markup when media item has no responsive images', function () {
        $page = Page::factory()->create();

        $file = UploadedFile::fake()->image('non-responsive.jpg', 800, 600);
        $media = $page->addMedia($file)
            ->toMediaCollection('documents');

        $html = sprintf(
            '<img data-id="%s" alt="Fallback Alt" />',
            $media->uuid,
        );

        $page->update(['content' => $html]);

        $rendered = $page->renderResponsiveRichContent();

        expect($rendered)
            ->toContain('src="'.$media->getUrl().'"')
            ->toContain('alt="Fallback Alt"')
            ->toContain('class="img-fluid"')
            ->toContain('loading="lazy"')
            ->not->toContain('srcset=')
            ->not->toContain('sizes=');
    });

    test('it renders images from other models with one batched media query', function () {
        $page = Page::factory()->create();
        $firstOwner = Page::factory()->create();
        $secondOwner = Page::factory()->create();

        $firstMedia = $firstOwner->addMedia(UploadedFile::fake()->image('first.jpg'))
            ->toMediaCollection('documents');
        $secondMedia = $secondOwner->addMedia(UploadedFile::fake()->image('second.jpg'))
            ->toMediaCollection('documents');

        $page->update([
            'content' => sprintf(
                '<img data-id="%s" alt="First image" /><img data-id="%s" alt="Second image" />',
                $firstMedia->uuid,
                $secondMedia->uuid,
            ),
        ]);
        $page = $page->fresh();

        $mediaQueries = [];
        DB::listen(function (QueryExecuted $query) use (&$mediaQueries): void {
            $sql = mb_strtolower($query->sql);

            if (str_contains($sql, 'from "media"') && str_contains($sql, 'uuid')) {
                $mediaQueries[] = $query;
            }
        });

        $rendered = $page->renderResponsiveRichContent();

        expect($mediaQueries)
            ->toHaveCount(1)
            ->and($mediaQueries[0]->sql)->toContain(' in ');

        expect($rendered)
            ->toContain($firstMedia->getUrl())
            ->toContain($secondMedia->getUrl());
    });
});

describe('webp images', function () {
    test('it renders webp conversion and webp responsive images when available', function () {
        $page = Page::factory()->create();

        $file = UploadedFile::fake()->image('test-image.jpg', 1200, 800);
        $media = $page->addMedia($file)
            ->toMediaCollection('content');

        // Simulate generated webp conversion and responsive images on webp
        $media->markAsConversionGenerated('webp');
        $media->responsive_images = [
            'webp' => [
                'urls' => [
                    'test-image___webp_1200_800.webp',
                    'test-image___webp_800_533.webp',
                ],
            ],
        ];
        $media->save();

        $html = sprintf(
            '<img data-id="%s" alt="WebP Image" />',
            $media->uuid,
        );

        $page->update(['content' => $html]);

        $rendered = $page->renderResponsiveRichContent();

        expect($rendered)
            ->toContain('src="'.$media->getUrl('webp').'"')
            ->toContain('alt="WebP Image"')
            ->toContain('srcset=')
            ->toContain('test-image___webp_1200_800.webp');
    });

    test('it renders webp conversion src with original responsive srcset fallback', function () {
        $page = Page::factory()->create();

        $file = UploadedFile::fake()->image('test-image.jpg', 1200, 800);
        $media = $page->addMedia($file)
            ->toMediaCollection('content');

        // Simulate generated webp conversion and responsive images on original only
        $media->markAsConversionGenerated('webp');
        $media->responsive_images = [
            'media_library_original' => [
                'urls' => [
                    'test-image___media_library_original_1200_800.jpg',
                ],
            ],
        ];
        $media->save();

        $html = sprintf(
            '<img data-id="%s" alt="WebP Image Original Fallback" />',
            $media->uuid,
        );

        $page->update(['content' => $html]);

        $rendered = $page->renderResponsiveRichContent();

        expect($rendered)
            ->toContain('src="'.$media->getUrl('webp').'"')
            ->toContain('srcset=')
            ->toContain('test-image___media_library_original_1200_800.jpg');
    });
});

describe('edge cases', function () {
    test('it leaves image untouched if data-id does not match any media', function () {
        $page = Page::factory()->create([
            'content' => '<img data-id="00000000-0000-0000-0000-000000000000" alt="Missing" />',
        ]);

        $rendered = $page->renderResponsiveRichContent();

        expect($rendered)
            ->toContain('alt="Missing"')
            ->toContain('data-id="00000000-0000-0000-0000-000000000000"');
    });

    test('it leaves images without data-id or uuid untouched', function () {
        $page = Page::factory()->create([
            'content' => '<img src="https://example.com/external.jpg" alt="External Image" />',
        ]);

        $rendered = $page->renderResponsiveRichContent();

        expect($rendered)
            ->toContain('src="https://example.com/external.jpg"')
            ->toContain('alt="External Image"');
    });
});
