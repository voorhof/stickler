<?php

namespace App\Http\CacheProfiles;

use Illuminate\Http\Request;
use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;

class CacheAllSuccessfulGetRequestsWithoutCart extends CacheAllSuccessfulGetRequests
{
    /**
     * Visitors with a cart cookie get personalised pages (cart badge), so they bypass the response cache.
     */
    public function enabled(Request $request): bool
    {
        return parent::enabled($request) && ! $request->cookies->has('cart_token');
    }
}
