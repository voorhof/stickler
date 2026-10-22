<?php

namespace App\Traits;

/**
 * Centralizes standard media collection definitions across models.
 *
 * Instead of each model independently defining identical media collections
 * (cover, images, videos, documents, content), this trait provides a single
 * method to register them all. Adding or modifying a collection is done in one place.
 *
 * Combine with `HasMediaConversions` to also centralize media conversions.
 *
 * @example
 * // In a model that uses InteractsWithMedia:
 * use HasStandardMediaCollections;
 * use HasMediaConversions;
 */
trait HasStandardMediaCollections
{
    /**
     * Register the standard media collections: avatar, cover, images, videos, documents, and content.
     */
    public function registerMediaCollections(): void
    {
        // Avatar single-file collection
        $this->addMediaCollection('avatar')
            ->useDisk(config('media-library.disk_name'))
            ->singleFile()
            ->withResponsiveImages()
            ->useFallbackUrl('https://ui-avatars.com/api/?name='.(mb_substr($this->name, 0, 1) ?: 'X').'&format=svg&color=FFFFFF&background=0b0809');

        // Cover image single-file collection
        $this->addMediaCollection('cover')
            ->useDisk(config('media-library.disk_name'))
            ->singleFile()
            ->withResponsiveImages()
            ->useFallbackUrl('/art/placeholders/pietje-200.png')
            ->useFallbackPath(public_path('/art/placeholders/pietje-200.png'));

        // Images multi-file collection
        $this->addMediaCollection('images')
            ->useDisk(config('media-library.disk_name'))
            ->withResponsiveImages();

        // Videos multi-file collection
        $this->addMediaCollection('videos')
            ->useDisk(config('media-library.disk_name'));

        // Documents multi-file collection
        $this->addMediaCollection('documents')
            ->useDisk(config('media-library.disk_name'));

        // Rich content collection
        $this->addMediaCollection('content')
            ->useDisk(config('media-library.disk_name'))
            ->withResponsiveImages();
    }
}
