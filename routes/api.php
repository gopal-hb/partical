<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Cache;
use App\Jobs\SyncPostsJob;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('/login', [AuthController::class, 'login']);

Route::middleware([
    'auth:sanctum',
    'throttle:60,1'
])->prefix('v1')->group(function () {

    Route::get('/posts', [PostController::class, 'index']);

    Route::get('/posts/{id}', [PostController::class, 'show']);

});

//Alternative — Force Sync Endpoint

Route::post('/sync-posts', function () {

    if (!Cache::has('posts_last_sync')) {
    SyncPostsJob::dispatch();
    }
    return response()->json([
        'message' => 'Sync started'
    ]);
});