<?php

namespace App\Observers;

use App\Models\Post;
use App\Observers\Traits\SyncsRichContentImageAltText;
use Exception;
use Illuminate\Support\Facades\Auth;

class PostObserver
{
    use SyncsRichContentImageAltText;

    /**
     * Handle the Post "creating" event.
     * Sets the creator and initial updater to the authenticated user.
     */
    public function creating(Post $post): void
    {
        if (Auth::check()) {
            $post->created_by_user_id = Auth::id();
            $post->updated_by_user_id = Auth::id();
        }
    }

    /**
     * Handle the Post "updating" event.
     * Updates the updater to the current authenticated user.
     */
    public function updating(Post $post): void
    {
        if (Auth::check()) {
            $post->updated_by_user_id = Auth::id();
        }
    }

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
