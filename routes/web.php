<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\TagsController;
use App\Http\Controllers\Admin\PostsController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;

Route::get('', [HomeController::class, 'index'])->name('home');
Route::get('show/{post:slug}', [HomeController::class, 'show'])->name('post.show');
Route::get('tag/{tag:slug}', [HomeController::class, 'tag'])->name('tag.show');
Route::get('category/{category:slug}', [HomeController::class, 'category'])->name('category.show');

Route::prefix('admin')->group(function () {
    Route::get('', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('categories', CategoryController::class);
    Route::resource('tags', TagsController::class);
    Route::resource('users', UsersController::class);
    Route::resource('posts', PostsController::class);
});



Auth::routes();
