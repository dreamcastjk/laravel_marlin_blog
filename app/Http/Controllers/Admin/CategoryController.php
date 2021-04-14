<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\CategoryRequest;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        $categories = Category::all();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        return view('admin.categories.create');
    }

    /**
     * @param CategoryRequest $request
     * @return RedirectResponse
     */
    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::create($request->all());
        return redirect()->route('categories.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return View
     */
    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * @param CategoryRequest $request
     * @param Category $category
     * @return RedirectResponse
     */
    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->all());

        return redirect()->route('categories.edit', $category);
    }

    /**
     * @param Category $category
     * @return \Illuminate\Contracts\Foundation\Application|RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('categories.index');
    }
}
