<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('account.dashboard').'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            activity('authentication')
                ->by(Auth::user())
                ->on(Auth::user())
                ->event('verification')
                ->log(__('User account verified.'));

            event(new Verified($request->user()));
        }

        return redirect()->intended(route('account.dashboard').'?verified=1');
    }
}
