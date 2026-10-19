<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Laravel\Head\Facades\Head;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::where('published_at', '<=', now())
            ->with('media')
            ->orderBy('order_column', 'asc')
            ->get();

        Head::title(__('Blogposts'))
            ->description(__('Blogposts').' '.config('app.name'));

        return view('public.posts.index', [
            'posts' => $posts,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        if ($post->published_at > now() || $post->published_at === null) {
            return redirect()->route('posts.index');
        }

        Head::title($post->title)
            ->description($post->intro);

        return view('public.posts.show', [
            'post' => $post,
        ]);
    }
}
