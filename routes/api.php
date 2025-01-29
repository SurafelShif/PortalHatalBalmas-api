<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::get('/user', 'user')->middleware(['auth:api']);
});
Route::controller(PostsController::class)->prefix('news')->group(function () {
    Route::get('/', 'getNews');
});
