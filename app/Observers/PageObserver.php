<?php

namespace App\Observers;

use App\Models\Page;
use App\Observers\Traits\SyncsRichContentImageAltText;
use Exception;
use Illuminate\Support\Facades\Auth;

class PageObserver
{
    use SyncsRichContentImageAltText;

    /**
     * Handle the Page "creating" event.
     * Sets the creator and initial updater to the authenticated user.
     *
     * @throws Exception
     */
    public function creating(Page $page): void
    {
        if (Auth::check()) {
            $page->created_by_user_id = Auth::id();
            $page->updated_by_user_id = Auth::id();
        }

        $excludedSlugs = config('sluggable.excluded_from_slug');

        if (in_array($page->slug, $excludedSlugs)) {
            $page->slug = $page->slug.now()->format('-y-m-d-H-i-s');
        }
    }

    /**
     * Handle the Page "updating" event.
     * Updates the updater to the current authenticated user.
     *
     * @throws Exception
     */
    public function updating(Page $page): void
    {
        if (Auth::check()) {
            $page->updated_by_user_id = Auth::id();
        }

        $excludedSlugs = config('sluggable.excluded_from_slug');

        if (in_array($page->slug, $excludedSlugs)) {
            $page->slug = $page->slug.now()->format('-y-m-d-H-i-s');
        }
    }

    /**
     * Copy each rich-content image's `alt` text into the `name`
     * column of the matching media record (matched by UUID / data-id).
     *
     * @throws Exception
     */
    public function saved(Page $page): void
    {
        $this->syncImageAltToMediaName($page); // defaults to ['content']

        // e.g. a model with two rich-content fields:
        // $this->syncImageAltToMediaName($page, ['content', 'method']);
    }
}
