<?php

use App\Http\Controllers\Api\V1\Admin\Auth\LoginController;
use App\Http\Controllers\Api\V1\Admin\Auth\PasswordController;
use App\Http\Controllers\Api\V1\Admin\Auth\RegisterController;
use App\Http\Controllers\Api\V1\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');







Route::group(['prefix' => 'v1/admin', 'middleware' => 'setAppLocale'], function () {
    Route::post('/login', [LoginController::class, 'store']);
    Route::post('/register', [RegisterController::class, 'store']);
    Route::post('/password/reset', [PasswordController::class, 'sendResetRequest']);
    Route::post('/password/reset/verify', [PasswordController::class, 'verifyResetCode']);
    Route::post('/password/reset/complete', [PasswordController::class, 'completeReset']);
    
    Route::get('/categories', [CategoryController::class, 'index']);

    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::post('/categories/store', [CategoryController::class, 'store']);
        Route::post('/categories/update/{id}', [CategoryController::class, 'update']);
    });
});

// Protected routes (require auth)

