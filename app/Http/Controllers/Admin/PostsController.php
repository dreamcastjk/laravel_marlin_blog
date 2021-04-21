<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Tag;
use App\Models\Post;
use App\Models\Category;
use App\Http\Requests\PostRequest;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Foundation\Application;

class PostsController extends Controller
{
    /**
     * @return View
     */
    public function index(): View
    {
        $posts = Post::all();

        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $tags = Tag::pluck('title', 'id')->all();
        $categories = Category::pluck('title', 'id')->all();

        return view(
            'admin.posts.create',
            compact(
            'tags',
            'categories'
            )
        );
    }

    /**
     * @param PostRequest $request
     *
     * @return RedirectResponse
     */
    public function store(PostRequest $request): RedirectResponse
    {
        $post = Post::add($request->validated());
        $post->uploadImage($request->file('image'));
        $post->setCategory($request->get('category_id'));
        $post->setTags($request->get('tags'));
        $post->toggleStatus($request->get('status'));
        $post->toggleFeatured($request->get('is_featured'));

        return redirect()->route('posts.index');
    }

    /**
     * @param Post $post
     * @return Application|Factory|View
     */
    public function edit(Post $post): View
    {
        $tags = Tag::pluck('title', 'id')->all();
        $categories = Category::pluck('title', 'id')->all();

        $selectedTags = $post->tags->pluck('id')->all();

        return view('admin.posts.edit', compact(
            'post',
            'tags',
            'categories',
            'selectedTags'
        ));
    }

    /**
     * @param PostRequest $request
     * @param Post $post
     *
     * @return RedirectResponse
     */
    public function update(PostRequest $request, Post $post): RedirectResponse
    {
        $post->edit($request->validated());
        $post->uploadImage($request->file('image'));
        $post->setCategory($request->get('category_id'));
        $post->setTags($request->get('tags'));
        $post->toggleStatus($request->get('status'));
        $post->toggleFeatured($request->get('is_featured'));

        return redirect()->route('posts.edit', $post);
    }

    /**
     * @param Post $post
     *
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(Post $post): RedirectResponse
    {
        $post->remove();

        return redirect()->route('posts.index');
    }
}
