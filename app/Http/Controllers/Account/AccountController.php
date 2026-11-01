<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdateProfileRequest;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        $user = Auth::user();

        $latestOrders = Order::where('user_id', $user->id)
            ->latest()
            ->take(2)
            ->get();

        $cart = Cart::where('user_id', $user->id)
            ->where('is_guest', false)
            ->first();
        $hasCartItems = $cart && $cart->totalQuantity() > 0;

        return view('account.dashboard', compact('latestOrders', 'hasCartItems'));
    }

    /**
     * Show the user's profile edit page.
     */
    public function profile(): View
    {
        Head::title(__('Edit profile'));

        return view('account.profile.edit');
    }

    /**
     * Update the user's profile information.
     */
    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->name = $request->string('name')->value();
        $user->email = $request->string('email')->value();
        $user->save();

        if ($request->filled('password')) {
            $user->password = Hash::make($request->string('password')->value());
            $user->save();

            activity('authentication')
                ->by(Auth::user())
                ->on(Auth::user())
                ->event('password update')
                ->log(__('User account password updated via profile page.'));
        }

        return redirect()->route('account.profile')
            ->with('status', 'profile-updated');
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
