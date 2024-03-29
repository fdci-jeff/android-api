<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AmadeusController; 
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;


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

Route::middleware('api')->prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('logOutAll', [AuthController::class, 'logOutAll']);
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('token/issue', [AuthController::class, 'tokenIssue']);
});

Route::middleware('api')->prefix('user')->group(function () {
    Route::post('get_user', [UserController::class, 'user']);
    Route::get('get_news', [UserController::class, 'getNews']);
    Route::get('get_latest_news', [UserController::class, 'getLatestNews']);
});

Route::any('{any}', function () {
    return sendError('Resource not found', '', 404);
})->where('any', '.*');
