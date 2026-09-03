<?php

namespace App\Actions\RichContent;

use App\Support\RichContent\RichContentImages;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SyncImageAltToMediaName
{
    /**
     * Copy each rich-content image's `alt` text into the `name` column
     * of the matching media record (matched by UUID / data-id).
     *
     * @param  array<int, string>  $attributes  Rich-content attributes to scan.
     */
    public function handle(Model $model, array $attributes = ['content']): void
    {
        if (! $model instanceof HasMedia) {
            return;
        }

        $map = [];

        foreach ($attributes as $attribute) {
            // += on the arrays merges the maps while preserving each UUID key,
            // so a model with multiple rich-content fields (e.g. content and intro) is handled in one pass.
            $map += RichContentImages::altByUuid($model->getAttribute($attribute));
        }

        if ($map === []) {
            return;
        }

        Media::query()
            ->where('model_type', $model->getMorphClass())
            ->where('model_id', $model->getKey())
            ->whereIn('uuid', array_keys($map))
            ->get()
            ->each(function (Media $media) use ($map): void {
                $alt = $map[$media->uuid] ?? null;

                if (filled($alt) && $media->name !== $alt) {
                    $media->update(['name' => $alt]);
                }
            });
    }
}
