<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index(): Renderable
    {
        $posts = Post::with(['category', 'tags'])->paginate(2);

        return view('pages.index', compact('posts'));
    }

    /**
     * @param Post $post
     * @return Renderable
     */
    public function show(Post $post): Renderable
    {
        return view('pages.show', compact('post'));
    }
}
