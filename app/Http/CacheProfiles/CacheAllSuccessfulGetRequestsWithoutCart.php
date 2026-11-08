<?php

namespace App\Http\CacheProfiles;

use Illuminate\Http\Request;
use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;

class CacheAllSuccessfulGetRequestsWithoutCart extends CacheAllSuccessfulGetRequests
{
    /**
     * Visitors with a cart cookie, a logged-in account, or session notifications get personalised pages, so they bypass the response cache.
     */
    public function enabled(Request $request): bool
    {
        return parent::enabled($request)
            && ! $request->cookies->has('cart_token')
            && ! auth()->check()
            && ! $this->hasFlashMessages($request);
    }

    /**
     * Determine if the request contains flash session notifications.
     */
    protected function hasFlashMessages(Request $request): bool
    {
        if (! $request->hasSession()) {
            return false;
        }

        $session = $request->session();

        return $session->has('success')
            || $session->has('error')
            || $session->has('status')
            || $session->has('errors')
            || $session->has('alert');
    }
}
