<?php

namespace App\Observers;

use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class MessageObserver
{
    /**
     * Handle the Message "creating" event.
     * Sets the creator and initial updater to the authenticated user.
     */
    public function creating(Message $message): void
    {
        if (Auth::check()) {
            $message->created_by_user_id = Auth::id();
            $message->updated_by_user_id = Auth::id();
        }
    }

    /**
     * Handle the Message "updating" event.
     * Updates the updater to the current authenticated user.
     */
    public function updating(Message $message): void
    {
        if (Auth::check()) {
            $message->updated_by_user_id = Auth::id();
        }
    }
}
