<?php

use App\Http\Controllers\auth\AuthController;
use App\Http\Controllers\task\TaskController;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('user')->group(function () {
    Route::post('registration', [AuthController::class, 'registration']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')
    ->group(function () {

        Route::prefix('user')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
        });

        Route::prefix('task')->group(function () {
            Route::get('index', [TaskController::class, 'index']);
            Route::post('create', [TaskController::class, 'store']);
            Route::patch('update/{task}', [TaskController::class, 'update']);
            Route::delete('destroy/{task}', [TaskController::class, 'destroy']);
            Route::get('search', [TaskController::class, 'search']);
        });
    });
