<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreOrderRequest;
use App\Models\CartItem;
use App\Models\Order;
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
        $cart->load('cartItems.product.media');

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
     * Display the order confirmation page.
     */
    public function checkout(): RedirectResponse|View
    {
        Head::title(__('Checkout'))
            ->description(__('Review your order and provide your details'));

        $cart = $this->cartService->getOrCreateCart(Auth::user());

        if ($cart->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', __('Your cart is empty'));
        }

        $cart->load('cartItems.product.media');

        return view('public.checkout.index', [
            'cart' => $cart,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Create an order from the cart.
     *
     * @throws Throwable
     */
    public function storeOrder(StoreOrderRequest $request): RedirectResponse
    {
        $cart = $this->cartService->getOrCreateCart(Auth::user());

        if ($cart->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', __('Your cart is empty'));
        }

        $validated = $request->validated();

        // Build customer data from the form
        $customerData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'shipping_address' => [
                'street' => $validated['shipping_street'],
                'city' => $validated['shipping_city'],
                'postal_code' => $validated['shipping_postal_code'],
                'country' => $validated['shipping_country'],
            ],
            'billing_same_as_shipping' => $request->boolean('billing_same_as_shipping'),
        ];

        if ($request->boolean('billing_same_as_shipping')) {
            $customerData['billing_address'] = $customerData['shipping_address'];
        } else {
            $customerData['billing_address'] = [
                'street' => $validated['billing_street'],
                'city' => $validated['billing_city'],
                'postal_code' => $validated['billing_postal_code'],
                'country' => $validated['billing_country'],
            ];
        }

        if (! empty($validated['notes'])) {
            $customerData['notes'] = $validated['notes'];
        }

        // Save customer data to the cart
        $this->cartService->saveCustomerData($cart, $customerData);

        // Convert cart to order (transfers customer data)
        $order = $this->cartService->convertToOrder($cart);

        return redirect()
            ->route('order.confirmed', ['order' => $order])
            ->with('success', __('Order created successfully'));
    }

    /**
     * Display the thank-you page after order creation.
     */
    public function thankYou(Order $order): View
    {
        Head::title(__('Thank you for your order'))
            ->description(__('Your order has been placed successfully'));

        $order->load('orderItems');

        return view('public.checkout.thankyou', [
            'order' => $order,
        ]);
    }
}
