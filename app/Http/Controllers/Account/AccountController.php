<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
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

        return view('account.dashboard');
    }
}
