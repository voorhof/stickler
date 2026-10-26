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
        $pages = Page::isPublicPublished()
            ->with('media')
            ->orderBy('order_column', 'asc')
            ->paginate(6);

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
        abort_unless($page->isPublished(), 404);

        Head::title($page->title)
            ->description($page->intro);

        return view('public.pages.show', [
            'page' => $page,
        ]);
    }
}
