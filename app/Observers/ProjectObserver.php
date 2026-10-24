<?php

namespace App\Observers;

use App\Models\Project;
use App\Observers\Traits\SyncsRichContentImageAltText;
use App\Observers\Traits\TracksCreatorAndUpdaterObserver;
use Exception;

class ProjectObserver
{
    use SyncsRichContentImageAltText;
    use TracksCreatorAndUpdaterObserver;

    /**
     * Copy each rich-content image's `alt` text into the `name`
     * column of the matching media record (matched by UUID / data-id).
     *
     * @throws Exception
     */
    public function saved(Project $project): void
    {
        $this->syncImageAltToMediaName($project); // defaults to ['content']

        // e.g. a model with two rich-content fields:
        // $this->syncImageAltToMediaName($project, ['content', 'method']);
    }
}
