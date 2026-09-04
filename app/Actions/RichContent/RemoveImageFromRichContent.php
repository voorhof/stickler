<?php

namespace App\Actions\RichContent;

use App\Support\RichContent\RichContentImages;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

class RemoveImageFromRichContent
{
    /**
     * Remove the image matching the media UUID from the model's rich content attributes.
     *
     * @param  array<int, string>  $attributes
     */
    public function handle(Model $model, string $uuid, array $attributes = ['content']): void
    {
        if (! $model instanceof HasMedia) {
            return;
        }

        $updated = false;

        foreach ($attributes as $attribute) {
            $content = $model->getAttribute($attribute);

            if (! is_string($content) || blank($content)) {
                continue;
            }

            $newContent = RichContentImages::removeImageByUuid($content, $uuid);

            if ($newContent !== $content) {
                $model->setAttribute($attribute, $newContent);
                $updated = true;
            }
        }

        if ($updated) {
            $model->saveQuietly();
        }
    }
}
