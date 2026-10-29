<?php

use App\Http\CacheProfiles\CacheAllSuccessfulGetRequestsWithoutCart;
use App\Models\User;
use Illuminate\Http\Request;

beforeEach(function () {
    config(['responsecache.enabled' => true]);

    $this->cacheProfile = new CacheAllSuccessfulGetRequestsWithoutCart;
});

describe('cache profile', function () {
    it('enables the response cache for a GET request without a cart cookie', function () {
        $request = Request::create('/');

        expect($this->cacheProfile->enabled($request))->toBeTrue();
    });

    it('disables the response cache for a GET request with a cart cookie', function () {
        $request = Request::create('/', 'GET', [], ['cart_token' => 'some-token']);

        expect($this->cacheProfile->enabled($request))->toBeFalse();
    });

    it('disables the response cache for a logged in user', function () {
        $this->actingAs(User::factory()->create());

        $request = Request::create('/', 'GET');

        expect($this->cacheProfile->enabled($request))->toBeFalse();
    });

    it('does not cache a non-GET request', function () {
        $request = Request::create('/', 'POST');

        expect($this->cacheProfile->shouldCacheRequest($request))->toBeFalse();
    });

    it('disables the response cache when response caching is turned off', function () {
        config(['responsecache.enabled' => false]);

        $request = Request::create('/');

        expect($this->cacheProfile->enabled($request))->toBeFalse();
    });
});
