<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdateProfileRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
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

        $newEmail = $request->string('email')->value();
        $emailChanged = $user->email !== $newEmail;

        $user->name = $request->string('name')->value();
        $user->email = $newEmail;

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

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
     * Delete the user's account.
     */
    public function destroyProfile(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->hasRole('Admin') && User::role('Admin')->withoutTrashed()->where('id', '!=', $user->id)->count() === 0) {
            return back()->withErrors(['deletion' => __('The last remaining administrator cannot be deleted.')], 'userDeletion');
        }

        activity('authentication')
            ->by($user)
            ->on($user)
            ->event('delete')
            ->log(__('User account deleted.'));

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
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
