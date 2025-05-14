<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
Route::middleware('api')->post('login', [AuthController::class, 'login']);

Route::middleware('api', 'auth:sanctum')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::get('user', [AuthController::class, 'getUser']);    
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::prefix('v1')->middleware(['api', 'auth:sanctum'])->group(function () {

    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{category}', [CategoryController::class, 'show']);

        Route::middleware('admin')->group(function () {
            Route::post('/', [CategoryController::class, 'store']);
            Route::post('/bulk', [CategoryController::class, 'bulkStore']);
            Route::put('/{category}', [CategoryController::class, 'update']);
            Route::patch('/{category}', [CategoryController::class, 'update']);
            Route::delete('/{category}', [CategoryController::class, 'destroy']);
        });
    });

    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{product}', [ProductController::class, 'show']);

        Route::middleware('admin')->group(function () {
            Route::post('/', [ProductController::class, 'store']);
            Route::post('/bulk', [ProductController::class, 'bulkStore']);
            Route::put('/{product}', [ProductController::class, 'update']);
            Route::patch('/{product}', [ProductController::class, 'update']);
            Route::delete('/{product}', [ProductController::class, 'destroy']);
        });
    });
});