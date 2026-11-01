<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Laravel\Head\Facades\Head;

class AccountController extends Controller
{
    /**
     * Show the user account dashboard page.
     */
    public function dashboard(): View
    {
        Head::title('Dashboard');

        return view('account.dashboard');
    }

    /**
     * Show the user's order history.
     */
    public function orders(): View
    {
        Head::title(__('My orders'));

        $orders = Order::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('account.orders.index', compact('orders'));
    }

    /**
     * Show the order detail page.
     */
    public function order(Order $order): View|RedirectResponse
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        Head::title(__('Order').' '.$order->order_number);

        return view('account.orders.show', compact('order'));
    }
}
