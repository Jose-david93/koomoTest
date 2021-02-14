<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;

Route::post("/login", [AuthController::class, 'login']);
Route::get("/posts", [PostController::class, 'index']);
Route::get("/posts/{id}", [PostController::class, 'show']);
Route::get('/commentsByUser/{id}', [CommentController::class, 'showByUserId']);
Route::get('/commentsByPost/{id}', [CommentController::class, 'showByPostId']);

Route::group(['middleware' => ['auth:sanctum']], function() {
    //Post
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    
    //Comment
    Route::post('/comments', [CommentController::class, 'store']);
    Route::put('/comments/{id}', [CommentController::class, 'update']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

    Route::get("/logout", [AuthController::class, 'logout']);
});
