<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Laravel\Head\Facades\Head;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pages = Page::where('published_at', '<=', now())
            ->with('media')
            ->orderBy('order_column', 'asc')
            ->get();

        Head::title(__('Pages'))
            ->description(__('Pages').' '.config('app.name'));

        return view('public.pages.index', [
            'pages' => $pages,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Page $page)
    {
        if ($page->published_at > now() || $page->published_at === null) {
            return redirect()->route('welcome.index');
        }

        Head::title($page->title)
            ->description($page->intro);

        return view('public.pages.show', [
            'page' => $page,
        ]);
    }
}
