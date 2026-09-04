<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    /**
     * Show the welcome page.
     */
    public function index(): View
    {
        return view('public.welcome', [
            'headTitle' => __('Welcome'),
        ]);
    }
}
