<?php

namespace App\Observers;

use App\Actions\RichContent\RemoveImageFromRichContent;
use App\Models\Media;
use Exception;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\HasMedia;

class MediaObserver
{
    /**
     * Handle the Media "creating" event.
     * Sets the creator and initial updater to the authenticated user.
     */
    public function creating(Media $media): void
    {
        if (Auth::check()) {
            $media->created_by_user_id = Auth::id();
            $media->updated_by_user_id = Auth::id();
        }
    }

    /**
     * Handle the Media "updating" event.
     * Updates the updater to the current authenticated user.
     */
    public function updating(Media $media): void
    {
        if (Auth::check()) {
            $media->updated_by_user_id = Auth::id();
        }
    }

    /**
     * Handle the Media "deleted" event.
     * Removes the image matching the media UUID from the model's rich content attributes.
     *
     * @throws Exception
     */
    public function deleted(Media $media): void
    {
        if ($media->collection_name !== 'content') {
            return;
        }

        $parent = $media->model;

        if ($parent instanceof HasMedia && method_exists($parent, 'hasRichContentAttribute')) {
            app(RemoveImageFromRichContent::class)->handle($parent, $media->uuid);
        }
    }
}
