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
Route::controller(PostController::class)->middleware(['auth:api', 'role:admin'])->prefix('posts')->group(function () {
    Route::post('/', 'createPost');
    Route::post('/{uuid}', 'updatePost');
    Route::delete('/{uuid}', 'deletePost');
    Route::middleware("throttle:20,1")->withoutMiddleware(['auth:api', 'role:admin'])->group(function () {
        Route::get('/', 'getPosts');
        Route::get('/{uuid}', 'getPostByUUid');
    });
});

Route::controller(AnnouncementController::class)->middleware(['auth:api', 'role:admin'])->prefix('announcements')->group(function () {
    Route::post('/update', 'updateAnnouncement');
    Route::middleware("throttle:20,1")->patch('/{uuid}', 'updateAnnouncementVisibility');
    Route::post('/', 'createAnnouncement');
    Route::delete('/{uuid}', 'deleteAnnouncement');
    Route::withoutMiddleware(['auth:api', 'role:admin'])->group(function () {
        Route::get('/', 'getAnnouncements');
        Route::get('/{uuid}', 'getAnnouncementByUUid');
    });
});
Route::controller(InformationController::class)->middleware(['auth:api', 'role:admin'])->prefix('info')->group(function () {
    Route::post('/', 'createInformation');
    Route::delete('/{uuid}', 'deleteInformation');
    Route::post('/{uuid}', 'updateInformation');
    Route::withoutMiddleware(['auth:api', 'role:admin'])->group(function () {
        Route::get('/', 'getInformations');
        Route::get('/{uuid}', 'getInformationByUUid');
    });
});
Route::controller(CategoryController::class)->middleware(['auth:api', 'role:admin'])->prefix('categories')->group(function () {
    Route::post('/', 'createCategory');
    Route::delete('/{categoryId}', 'deleteCategory');
    Route::put('/{categoryId}', 'updateCategory');
    Route::withoutMiddleware(['auth:api', 'role:admin'])->get('/', 'getCategories');
});
Route::controller(SiteController::class)->middleware(['auth:api', 'role:admin'])->prefix('sites')->group(function () {
    Route::post('/', 'createSite');
    Route::post('/{uuid}', 'updateSite');
    Route::delete('/{uuid}', 'deleteSite');
    Route::withoutMiddleware(['auth:api', 'role:admin'])->get('/', 'getSites');
});
