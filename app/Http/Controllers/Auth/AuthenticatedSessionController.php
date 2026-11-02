<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Head\Facades\Head;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        Head::title('Login');

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        activity('authentication')
            ->by(Auth::user())
            ->on(Auth::user())
            ->event('login')
            ->log(__('User account logged in.'));

        // Redirect to user dashboard
        return redirect()->intended(route('account.dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        activity('authentication')
            ->by(Auth::user())
            ->on(Auth::user())
            ->event('logout')
            ->log(__('User account logged out.'));

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Redirect to home page
        return redirect()->intended(route('welcome.index'));
    }
}
