<?php

namespace App\Actions\RichContent;

use App\Support\RichContent\RichContentImages;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

class CopyRichContentMedia
{
    /**
     * Copy any media referenced in rich content attributes that does not yet belong
     * to this model (e.g. copied from a template), creating new media records and
     * updating the rich content HTML references (data-id and src).
     *
     * @param  array<int, string>  $attributes
     */
    public function handle(Model $model, array $attributes = ['content']): void
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

            $newContent = RichContentImages::copyMediaForModel($content, $model);

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
