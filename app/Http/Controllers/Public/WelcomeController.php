<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Laravel\Head\Facades\Head;

class WelcomeController extends Controller
{
    /**
     * Show the welcome page.
     */
    public function index(): View
    {
        Head::title('Welkom');

        return view('public.welcome');
    }
}
