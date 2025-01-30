<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\SitesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::get('/user', 'user')->middleware(['auth:api']);
});
Route::controller(PostsController::class)->prefix('posts')->group(function () {
    Route::get('/', 'getPosts');
    Route::middleware("throttle:20,1")->get('/{uuid}', 'getPostByUUid');
});
Route::controller(SitesController::class)->prefix('sites')->group(function () {
    Route::get('/', 'getSites');
});
