<?php

use App\Http\Controllers\Admin\TagsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->group(function () {
    Route::get('', [DashboardController::class, 'index']);
    Route::resource('categories', CategoryController::class);
    Route::resource('tags', TagsController::class);
});


