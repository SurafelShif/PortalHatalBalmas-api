<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\SitesController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::get('/user', 'user')->middleware(['auth:api']);
});
Route::controller(PostsController::class)->prefix('Post')->group(function () {
    Route::get('/', 'getPost');
    Route::middleware("throttle:20,1")->get('/{uuid}', 'getPostByUUid');
});
Route::controller(SitesController::class)->prefix('sites')->group(function () {
    Route::get('/', 'getSites');
});
Route::controller(CategoryController::class)->prefix('categories')->group(function () {
    Route::get('/', 'getCategories');
    Route::post('/', 'createCategory');
    Route::delete('/{categoryId}', 'deleteCategory');
    Route::put('/{categoryId}', 'updateCategory');
});
