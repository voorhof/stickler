<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Head\Facades\Head;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        if ($request->user()->hasVerifiedEmail()) {
            activity('authentication')
                ->by(Auth::user())
                ->on(Auth::user())
                ->event('verification')
                ->log(__('User account verified.'));

            return redirect()->intended(route('account.dashboard'));
        }

        activity('authentication')
            ->by(Auth::user())
            ->on(Auth::user())
            ->event('verification')
            ->log(__('User account verification needed.'));

        Head::title('Bevestig e-mailadres');

        return view('auth.verify-email');
    }
}
