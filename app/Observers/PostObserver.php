<?php

namespace App\Observers;

use App\Models\Post;
use App\Observers\Traits\SyncsRichContentImageAltText;
use App\Observers\Traits\TracksCreatorAndUpdaterObserver;
use Exception;

class PostObserver
{
    use SyncsRichContentImageAltText;
    use TracksCreatorAndUpdaterObserver;

    /**
     * Copy each rich-content image's `alt` text into the `name`
     * column of the matching media record (matched by UUID / data-id).
     *
     * @throws Exception
     */
    public function saved(Post $post): void
    {
        $this->syncImageAltToMediaName($post); // defaults to ['content']

        // e.g. a model with two rich-content fields:
        // $this->syncImageAltToMediaName($post, ['content', 'method']);
    }
}
