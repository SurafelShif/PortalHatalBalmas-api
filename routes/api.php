<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GlobalController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\InformationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'loginAzure');
});
Route::controller(GlobalController::class)->middleware(['auth:api'])->group(function () {
    Route::get('/search', 'search');
});
Route::get('/user', [UserController::class, 'user'])->middleware(['auth:api']);

Route::controller(UserController::class)->middleware(['auth:api', 'role:admin'])->prefix('users')->group(function () {
    Route::post('/', 'addAdmin');
    Route::delete('/{uuid}', 'deleteAdmin');
    Route::get('/', 'index');
    Route::get("/{personal_id}", "getUserById");
});

Route::controller(PostController::class)->middleware(['auth:api', 'role:admin'])->prefix('posts')->group(function () {
    Route::post('/', 'createPost');
    Route::post('/{uuid}', 'updatePost');
    Route::delete('/{uuid}', 'deletePost');
    Route::get('/admin', 'getAdminPosts');
    Route::get('/', 'getPosts')->withoutMiddleware(['role:admin']);
    Route::get('/{uuid}', 'getPostByUUid')->withoutMiddleware(['role:admin']);
});

Route::controller(AnnouncementController::class)->middleware(['auth:api', 'role:admin'])->prefix('announcements')->group(function () {
    Route::post('/{uuid}', 'updateAnnouncement');
    Route::patch('/{uuid}', 'updateAnnouncementVisibility');
    Route::post('/', 'createAnnouncement');
    Route::delete('/{uuid}', 'deleteAnnouncement');
    Route::get('/admin', 'getAdminAnnouncements');
    Route::get('/', 'getAnnouncements')->withoutMiddleware(['role:admin']);
    Route::get('/{uuid}', 'getAnnouncementByUUid')->withoutMiddleware(['role:admin']);
    Route::put('/updatePosition', 'updateAnnouncementPosition');
});

Route::controller(InformationController::class)->middleware(['auth:api', 'role:admin'])->prefix('info')->group(function () {
    Route::post('/', 'createInformation');
    Route::delete('/{uuid}', 'deleteInformation');
    Route::post('/{uuid}', 'updateInformation');
    Route::get('/', 'getInformations')->withoutMiddleware(['role:admin']);
    Route::get('/{uuid}', 'getInformationByUUid')->withoutMiddleware(['role:admin']);
});

Route::controller(CategoryController::class)->middleware(['auth:api', 'role:admin'])->prefix('categories')->group(function () {
    Route::post('/', 'createCategory');
    Route::delete('/{category_uuid}', 'deleteCategory');
    Route::put('/{category_uuid}', 'updateCategory');
    Route::get('/{type}', 'getCategories')->withoutMiddleware(['role:admin']);
});

Route::controller(SiteController::class)->middleware(['auth:api', 'role:admin'])->prefix('sites')->group(function () {
    Route::post('/', 'createSite');
    Route::post('/{uuid}', 'updateSite');
    Route::delete('/{uuid}', 'deleteSite');
    Route::get('/', 'getSites')->withoutMiddleware(['role:admin']);
});
Route::controller(ImageController::class)->middleware(['auth:api', 'role:admin'])->prefix('images')->group(function () {
    Route::post("/", 'save');
});
