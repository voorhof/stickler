<?php

namespace App\Observers\Traits;

use App\Actions\RichContent\CopyRichContentMedia;
use App\Actions\RichContent\SyncImageAltToMediaName;
use Exception;
use Illuminate\Database\Eloquent\Model;

trait SyncsRichContentImageAltText
{
    /**
     * Copy each rich-content image's `alt` text into the `name` column of its media.
     *
     * @param  array<int, string>  $attributes
     *
     * @throws Exception
     */
    protected function syncImageAltToMediaName(Model $model, array $attributes = ['content']): void
    {
        app(SyncImageAltToMediaName::class)->handle($model, $attributes);
    }

    /**
     * Copy any media referenced in rich content attributes that does not yet belong to the model.
     *
     * @param  array<int, string>  $attributes
     *
     * @throws Exception
     */
    protected function copyRichContentMedia(Model $model, array $attributes = ['content']): void
    {
        app(CopyRichContentMedia::class)->handle($model, $attributes);
    }
}
