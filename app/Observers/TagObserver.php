<?php

namespace App\Observers;

use App\Models\Tag;
use Illuminate\Support\Facades\Auth;

class TagObserver
{
    /**
     * Handle the Tag "creating" event.
     * Sets the creator and initial updater to the authenticated user.
     */
    public function creating(Tag $tag): void
    {
        if (Auth::check()) {
            $tag->created_by_user_id = Auth::id();
            $tag->updated_by_user_id = Auth::id();
        }
    }

    /**
     * Handle the Tag "updating" event.
     * Updates the updater to the current authenticated user.
     */
    public function updating(Tag $tag): void
    {
        if (Auth::check()) {
            $tag->updated_by_user_id = Auth::id();
        }
    }
}
