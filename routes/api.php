<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\PasswordController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PageContentController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ProductController;

use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');







Route::group(['prefix' => 'v1/{locale?}', 'middleware' => 'setAppLocale'], function () {

    //*************************************Auh Routes*************************************
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:10,1');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1');



    //*************************************Public Routes*************************************
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);

    Route::get('/categories', [CategoryController::class, 'index']);

    Route::get('/page-content/{page}', [PageContentController::class, '__invoke']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart/add', [CartController::class, 'store']);
        Route::put('/cart/update/{cart}', [CartController::class, 'update']);
        Route::delete('/cart/delete', [CartController::class, 'destroy']);
        Route::delete('/cart/remove-item/{cart_item_id}', [CartController::class, 'removeItem']);
        Route::post('/logout', [LoginController::class, 'logout']);

        Route::post('/create-order', [OrderController::class, 'store']);
        Route::get('/orders', [OrderController::class, 'index']);
        // Route::post('/create-payment', [PaymentController::class, 'createPayment']);
        // Route::post('/payment-callback', [PaymentController::class, 'paymentCallback'])->name('payment.callback');
    });

});

// Protected routes (require auth)

