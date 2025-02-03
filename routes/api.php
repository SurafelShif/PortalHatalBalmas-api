<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::get('/user', 'user')->middleware(['auth:api']);
});
Route::controller(UserController::class)->middleware(['auth:api', 'role:admin'])->prefix('users')->group(function () {
    Route::post('/addAdmin', 'addAdmin');
});
Route::controller(PostController::class)->prefix('Post')->group(function () {
    Route::get('/', 'getPost');
    Route::middleware("throttle:20,1")->get('/{uuid}', 'getPostByUUid');
});
Route::controller(SiteController::class)->prefix('sites')->group(function () {
    Route::get('/', 'getSites');
});
Route::controller(CategoryController::class)->prefix('categories')->group(function () {
    Route::get('/', 'getCategories');
    Route::post('/', 'createCategory');
    Route::delete('/{categoryId}', 'deleteCategory');
    Route::put('/{categoryId}', 'updateCategory');
});
