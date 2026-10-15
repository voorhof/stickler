<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Settings\TermsSettings;
use Illuminate\View\View;
use Laravel\Head\Facades\Head;

class TermsController extends Controller
{
    /**
     * Show general terms page.
     */
    public function terms(): View
    {
        Head::title(__('Terms and conditions'))
            ->description(__('Terms and conditions').' | '.config('app.name'));

        return view('public.terms', [
            'content' => app(TermsSettings::class)->terms_and_conditions,
        ]);
    }

    /**
     * Show privacy policy page.
     */
    public function privacy(): View
    {
        Head::title(__('Privacy policy'))
            ->description(__('Privacy policy').' | '.config('app.name'));

        return view('public.terms', [
            'content' => app(TermsSettings::class)->privacy_policy,
        ]);
    }

    /**
     * Show cookie policy page.
     */
    public function cookie(): View
    {
        Head::title(__('Cookie policy'))
            ->description(__('Cookie policy').' | '.config('app.name'));

        return view('public.terms', [
            'content' => app(TermsSettings::class)->cookie_policy,
        ]);
    }
}
