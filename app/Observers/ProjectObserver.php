<?php

namespace App\Observers;

use App\Models\Project;
use App\Observers\Traits\SyncsRichContentImageAltText;
use Exception;
use Illuminate\Support\Facades\Auth;

class ProjectObserver
{
    use SyncsRichContentImageAltText;

    /**
     * Handle the Project "creating" event.
     * Sets the creator and initial updater to the authenticated user.
     */
    public function creating(Project $project): void
    {
        if (Auth::check()) {
            $project->created_by_user_id = Auth::id();
            $project->updated_by_user_id = Auth::id();
        }
    }

    /**
     * Handle the Project "updating" event.
     * Updates the updater to the current authenticated user.
     */
    public function updating(Project $project): void
    {
        if (Auth::check()) {
            $project->updated_by_user_id = Auth::id();
        }
    }

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
