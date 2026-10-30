<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Laravel\Head\Facades\Head;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::isPublicPublished()
            ->with(['media', 'categories'])
            ->orderBy('order_column', 'asc')
            ->paginate(6);

        Head::title(__('Products'))
            ->description(__('Products').' '.config('app.name'));

        return view('public.products.index', [
            'products' => $products,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        abort_unless($product->isPublished(), 404);

        $product->load(['variants.options', 'categories']);

        Head::title($product->title)
            ->description($product->intro);

        $cartItems = app(CartService::class)->findCurrentCart(Auth::user())?->cartItems ?? collect();

        return view('public.products.show', [
            'product' => $product,
            'cartItems' => $cartItems,
        ]);
    }
}
