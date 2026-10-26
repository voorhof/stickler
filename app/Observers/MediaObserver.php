<?php

/** @noinspection PhpParameterNameChangedDuringInheritanceInspection */

namespace App\Observers;

use App\Actions\RichContent\RemoveImageFromRichContent;
use App\Models\Media;
use App\Observers\Traits\FlushesResponseCache;
use App\Observers\Traits\TracksCreatorAndUpdaterObserver;
use Exception;
use Spatie\MediaLibrary\HasMedia;

class MediaObserver
{
    use FlushesResponseCache;
    use TracksCreatorAndUpdaterObserver;

    /**
     * Handle the Media "deleted" event.
     * Removes the image matching the media UUID from the model's rich content attributes.
     *
     * @throws Exception
     */
    public function deleted(Media $media): void
    {
        $this->flushResponseCache();

        if ($media->collection_name !== 'content') {
            return;
        }

        $parent = $media->model;

        if ($parent instanceof HasMedia && method_exists($parent, 'hasRichContentAttribute')) {
            app(RemoveImageFromRichContent::class)->handle($parent, $media->uuid);
        }
    }
}
