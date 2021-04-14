<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tag;
use App\Http\Requests\TagsRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TagsController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(): View
    {
        $tags = Tag::all();

        return view('admin.tags.index', compact('tags'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create(): View
    {
        return view('admin.tags.create');
    }

    /**
     * @param TagsRequest $request
     * @return RedirectResponse
     */
    public function store(TagsRequest $request): RedirectResponse
    {
        Tag::create($request->validated());

        return redirect()->route('tags.index');
    }

    /**
     * @param Tag $tag
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|View
     */
    public function edit(Tag $tag): View
    {
        return view('admin.tags.edit', compact('tag'));
    }

    /**
     * @param TagsRequest $request
     * @param Tag $tag
     * @return RedirectResponse
     */
    public function update(TagsRequest $request, Tag $tag): RedirectResponse
    {
        $tag->update($request->validated());

        return redirect()->route('tags.edit', $tag);
    }

    /**
     * @param Tag $tag
     * @return RedirectResponse
     * @throws \Exception
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return redirect()->route('tags.index');
    }
}
