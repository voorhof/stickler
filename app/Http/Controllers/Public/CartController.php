<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Head\Facades\Head;
use Throwable;

class CartController extends Controller
{
    /**
     * Create a new CartController instance.
     */
    public function __construct(
        protected CartService $cartService,
    ) {}

    /**
     * Display the cart page.
     */
    public function index(): View
    {
        Head::title(__('Cart'))
            ->description(__('View and manage your shopping cart'));

        $cart = $this->cartService->getOrCreateCart(Auth::user());
        $cart->load('items.product.media');

        return view('public.cart.index', ['cart' => $cart]);
    }

    /**
     * Add a product to the cart.
     *
     * @throws Exception
     */
    public function store(Request $request, Product $product, ?ProductVariant $productVariant = null): RedirectResponse
    {
        $quantity = $request->integer('quantity', 1);

        if ($productVariant === null && $product->variants()->exists()) {
            return redirect()
                ->back()
                ->with('error', __('Please choose a variant'));
        }

        abort_if($productVariant !== null && $productVariant->product_id !== $product->id, 404);

        $cart = $this->cartService->getOrCreateCart(Auth::user());
        $this->cartService->addItem($cart, $product, $productVariant, $quantity);

        $response = redirect()
            ->route('cart.index')
            ->with('success', __('Product added to cart'));

        if ($cart->is_guest) {
            $response->cookie('cart_token', $cart->cart_token, 30 * 24 * 60);
        }

        return $response;
    }

    /**
     * Update a cart item's quantity.
     *
     * @throws Exception
     */
    public function update(Request $request, CartItem $item): RedirectResponse
    {
        $quantity = $request->integer('quantity', 1);

        $cart = $this->cartService->getOrCreateCart(Auth::user());

        abort_unless($item->cart_id === $cart->id, 403);

        $this->cartService->updateItem($item, $quantity);

        return redirect()
            ->route('cart.index')
            ->with('success', __('Cart updated'));
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(CartItem $item): RedirectResponse
    {
        $cart = $this->cartService->getOrCreateCart(Auth::user());

        abort_unless($item->cart_id === $cart->id, 403);

        $this->cartService->removeItem($item);

        return redirect()
            ->route('cart.index')
            ->with('success', __('Item removed from cart'));
    }

    /**
     * Checkout — convert the cart to an order.
     *
     * @throws Throwable
     */
    public function checkout(): RedirectResponse
    {
        $cart = $this->cartService->getOrCreateCart(Auth::user());

        if ($cart->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', __('Your cart is empty'));
        }

        /** @noinspection PhpUnusedLocalVariableInspection */
        $order = $this->cartService->convertToOrder($cart);

        return redirect()
            ->route('products.index')
            ->with('success', __('Order created successfully'));
    }
}
