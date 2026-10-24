<?php

namespace App\Traits;

use Spatie\Image\Enums\Fit;

/**
 * Centralizes standard media conversion definitions across models.
 *
 * Instead of each model independently defining identical preview, thumb, and webp
 * conversions, this trait provides a single method to register them on any set
 * of media collections. Changing a dimension, format, or option is now done in
 * one place.
 *
 * @example
 * // In a model's registerMediaConversions():
 * $this->registerStandardMediaConversions(['cover', 'images', 'videos', 'documents', 'content']);
 */
trait HasMediaConversions
{
    /**
     * Register the standard preview, thumb, and webp conversions on the given collections.
     *
     * @param  array<string>  $collections  The media collection names to apply conversions to.
     */
    protected function registerStandardMediaConversions(array $collections): void
    {
        $this->addMediaConversion('preview')
            ->fit(Fit::Crop, 400, 400)
            ->nonQueued()
            ->deferred()
            ->format('webp')
            ->performOnCollections(...$collections)
            ->pdfPageNumber(1)
            ->extractVideoFrameAtSecond(1);

        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 40, 40)
            ->nonQueued()
            ->deferred()
            ->format('webp')
            ->performOnCollections(...$collections)
            ->pdfPageNumber(1)
            ->extractVideoFrameAtSecond(1);

        $webpCollections = array_values(array_intersect($collections, $this->webpCollections()));

        if ($webpCollections) {
            $this->addMediaConversion('webp')
                ->deferred()
                ->format('webp')
                ->withResponsiveImages()
                ->performOnCollections(...$webpCollections);
        }
    }

    /**
     * Get the collection names that should receive the webp responsive image conversion.
     *
     * By default, webp responsive images are generated for cover, images, and content.
     * Override this method in a model to customize the list.
     *
     * @return array<string>
     */
    protected function webpCollections(): array
    {
        return ['cover', 'images', 'content'];
    }
}
