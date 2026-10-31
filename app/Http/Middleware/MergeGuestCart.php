<?php

/** @noinspection PhpUndefinedMethodInspection */

namespace App\Http\Middleware;

use App\Services\CartService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MergeGuestCart
{
    /**
     * Handle an incoming request.
     *
     * Merges the guest cart into the user's authenticated cart when a guest with
     * a cart_token cookie logs in or registers. After the merge, the guest cart
     * cookie is cleared so the user continues with their authenticated cart.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! auth()->check()) {
            return $response;
        }

        $cartToken = $request->cookie('cart_token');

        if (! $cartToken) {
            return $response;
        }

        $cartService = app(CartService::class);
        $guestCart = $cartService->getByToken($cartToken);

        if (! $guestCart || ! $guestCart->is_guest || $guestCart->isEmpty()) {
            return $response;
        }

        $userCart = $cartService->getOrCreateCart(auth()->user());

        $cartService->merge($guestCart, $userCart);

        // Update the cart_token cookie to point to the user's authenticated cart
        $response->cookie('cart_token', $userCart->cart_token, 30 * 24 * 60);

        return $response;
    }
}
