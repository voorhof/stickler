<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Settings\TermsSettings;
use Illuminate\View\View;

class TermsController extends Controller
{
    /**
     * Show general terms page.
     */
    public function terms(): View
    {
        return view('public.terms', [
            'content' => app(TermsSettings::class)->terms_and_conditions,
            'headTitle' => __('Terms and conditions'),
        ]);
    }

    /**
     * Show privacy policy page.
     */
    public function privacy(): View
    {
        return view('public.terms', [
            'content' => app(TermsSettings::class)->privacy_policy,
            'headTitle' => __('Privacy policy'),
        ]);
    }

    /**
     * Show cookie policy page.
     */
    public function cookie(): View
    {
        return view('public.terms', [
            'content' => app(TermsSettings::class)->cookie_policy,
            'headTitle' => __('Cookie policy'),
        ]);
    }
}
