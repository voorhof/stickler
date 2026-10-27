<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Laravel\Head\Facades\Head;

class BrandbookController extends Controller
{
    /**
     * Show the brandbook page.
     */
    public function index(): View
    {
        Head::title('Brandbook');

        return view('public.brandbook');
    }
}
