<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InformationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::get('/user', 'user')->middleware(['auth:api']);
});
Route::controller(UserController::class)->middleware(['auth:api', 'role:admin'])->prefix('users')->group(function () {
    Route::post('/', 'addAdmin');
    Route::delete('/{personal_id}', 'deleteAdmin');
    Route::get('/', 'index');
});
//TODO if category not found then return error
Route::controller(PostController::class)->prefix('posts')->group(function () {
    Route::middleware("throttle:20,1")->get('/', 'getPosts');
    Route::post('/', 'createPost');
    Route::post('/{uuid}', 'updatePost');
    Route::delete('/{uuid}', 'deletePost');
    Route::middleware("throttle:20,1")->get('/{uuid}', 'getPostByUUid');
});
Route::controller(SiteController::class)->prefix('sites')->group(function () {
    Route::get('/', 'getSites');
    Route::post('/', 'createSite');
    Route::post('/{uuid}', 'updateSite');
    Route::delete('/{uuid}', 'deleteSite');
});
Route::controller(AnnouncementController::class)->prefix('announcements')->group(function () {
    Route::get('/', 'getAnnouncements');
    Route::post('/{uuid}', 'updateAnnouncement');
    Route::middleware("throttle:20,1")->patch('/{uuid}', 'updateAnnouncementVisibility');
    Route::post('/', 'createAnnouncement');
    Route::delete('/{uuid}', 'deleteAnnouncement');
});
Route::controller(CategoryController::class)->prefix('categories')->group(function () {
    Route::get('/', 'getCategories');
    Route::post('/', 'createCategory');
    Route::delete('/{categoryId}', 'deleteCategory');
    Route::put('/{categoryId}', 'updateCategory');
});
Route::controller(InformationController::class)->prefix('informations')->group(function () {
    Route::get('/', 'getInformations');
    // Route::post('/', 'createCategory');
    // Route::delete('/{categoryId}', 'deleteCategory');
    // Route::put('/{categoryId}', 'updateCategory');
});
