<?php

use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\PostViewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    Route::get('/feed', [FeedController::class, 'index']);

    Route::prefix('user')->group(function () {
        Route::get('/{userId}/feed', [FeedController::class, 'userFeed'])->name('api.feed.user');
        Route::post('/{userId}/view/{post}', [PostViewController::class, 'store'])->name('api.posts.view');
        Route::get('/{userId}/stats', [FeedController::class, 'userStats'])->name('api.user.stats');
    });
});
