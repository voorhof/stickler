<?php

namespace App\Http\Controllers\Public;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreOrderRequest;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
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
        protected PaymentService $paymentService,
    ) {}

    /**
     * Display the cart page.
     */
    public function index(): RedirectResponse|View
    {
        Head::title(__('Cart'))
            ->description(__('View and manage your shopping cart'));

        $cart = $this->cartService->getOrCreateCart(Auth::user());
        $cart->load('cartItems');

        $removedCount = $this->cartService->removeOutOfStockItems($cart);

        if ($removedCount > 0) {
            return redirect()
                ->route('cart.index')
                ->with('warning', __('Out of stock items removed from your cart', ['count' => $removedCount]));
        }

        $cart->load('cartItems');
        $adjustedCount = $this->cartService->adjustOverstockedItems($cart);

        $cart->load('cartItems.product.media', 'cartItems.productVariant');

        if ($adjustedCount > 0) {
            return redirect()
                ->route('cart.index')
                ->with('warning', __('Some items in your cart are no longer available in the requested quantity.'));
        }

        return view('public.cart.index', ['cart' => $cart]);
    }

    /**
     * Add a product to the cart.
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

        try {
            $this->cartService->addItem($cart, $product, $productVariant, $quantity);
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }

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
     */
    public function update(Request $request, CartItem $item): RedirectResponse
    {
        $quantity = $request->integer('quantity', 1);

        $cart = $this->cartService->getOrCreateCart(Auth::user());

        abort_unless($item->cart_id === $cart->id, 403);

        try {
            $this->cartService->updateItem($item, $quantity);
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }

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
        $cart->load('cartItems');

        $this->cartService->removeOutOfStockItems($cart);

        if ($cart->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', __('Your cart is empty'));
        }

        $cart->load('cartItems');
        $adjustedCount = $this->cartService->adjustOverstockedItems($cart);

        if ($cart->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', __('Your cart is empty'));
        }

        $cart->load('cartItems.product.media');

        $view = view('public.checkout.index', [
            'cart' => $cart,
            'user' => Auth::user(),
        ]);

        if ($adjustedCount > 0) {
            return redirect()
                ->route('checkout.index')
                ->with('warning', __('Some items in your cart are no longer available in the requested quantity.'));
        }

        return $view;
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
        try {
            $order = $this->cartService->convertToOrder($cart);
        } catch (InsufficientStockException $e) {
            return redirect()
                ->route('cart.index')
                ->with('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('cart.index')
                ->with('error', __('Unable to process order. Please check item availability.'));
        }

        // Store the last guest order ID in the session for thank-you page access
        if ($cart->is_guest) {
            request()->session()->put('last_guest_order_id', $order->id);
        }

        try {
            // Initiate payment and redirect to Mollie checkout
            $payment = $this->paymentService->initiatePayment($order);

            return redirect($payment['checkout_url']);
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('order.confirmed', $order)
                ->with('error', __('Payment initialization failed. You can try paying again below.'));
        }
    }

    /**
     * Retry payment for an unpaid order.
     */
    public function retryPayment(Request $request, Order $order): RedirectResponse
    {
        // Authenticated users can only pay for their own orders
        if (! Auth::check()) {
            // Guest users can access via session or signed URL
            $hasSessionAccess = $request->session()->get('last_guest_order_id') === $order->id;
            $hasValidSignature = $request->hasValidSignature();

            abort_unless($hasSessionAccess || $hasValidSignature, 403);
        }

        // Authenticated users can only pay for their own orders
        if (Auth::check()) {
            abort_unless($order->user_id === Auth::id(), 403);
        }

        if ($order->payment_status === PaymentStatus::Paid) {
            return redirect()
                ->route('order.confirmed', $order)
                ->with('success', __('Order is already paid'));
        }

        if ($order->status === OrderStatus::Cancelled) {
            return redirect()
                ->route('order.confirmed', $order)
                ->with('error', __('This order has been cancelled and cannot be paid. Please place a new order.'));
        }

        try {
            $payment = $this->paymentService->initiatePayment($order);

            return redirect($payment['checkout_url']);
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('order.confirmed', $order)
                ->with('error', __('Payment initialization failed. Please try again.'));
        }
    }

    /**
     * Display the thank-you page after order creation.
     */
    public function thankYou(Request $request, Order $order): View
    {
        Head::title(__('Thank you for your order'))
            ->description(__('Your order has been placed successfully'));

        // Authenticated users can only view their own orders
        if (! Auth::check()) {
            // Guest users can access via session or signed URL
            $hasSessionAccess = $request->session()->get('last_guest_order_id') === $order->id;
            $hasValidSignature = $request->hasValidSignature();

            abort_unless($hasSessionAccess || $hasValidSignature, 403);
        }

        // Authenticated users can only view their own orders
        if (Auth::check()) {
            abort_unless($order->user_id === Auth::id(), 403);
        }

        $order->load('orderItems');

        // Build the "Pay now" form action URL.
        // Authenticated users and guests with session access use the regular route.
        // Guests without session access get a temporary signed URL (30 days).
        $payNowUrl = route('checkout.pay', $order);
        if (! Auth::check() && $request->session()->get('last_guest_order_id') !== $order->id) {
            $payNowUrl = URL::temporarySignedRoute('checkout.pay', now()->addDays(30), ['order' => $order]);
        }

        return view('public.checkout.thankyou', [
            'order' => $order,
            'payNowUrl' => $payNowUrl,
        ]);
    }
}
