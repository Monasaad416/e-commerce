<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PageContentController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\StripeWebhookController;
use Illuminate\Support\Facades\Route;

// Stripe webhooks (no auth, no locale) — configure in Stripe Dashboard / Stripe CLI
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

Route::group(['prefix' => 'v1/{locale?}', 'middleware' => 'setAppLocale'], function () {
    // Auth
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:10,1');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1');

    // Public catalog
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/page-content/{page}', [PageContentController::class, '__invoke']);

    // Cart: guests (X-Cart-Token / cart_token cookie) + logged-in users (Bearer)
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'store']);
    Route::put('/cart/update', [CartController::class, 'update']);
    Route::delete('/cart/delete', [CartController::class, 'destroy']);
    Route::delete('/cart/remove-item/{cart_item_id}', [CartController::class, 'removeItem']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/cart/merge', [CartController::class, 'merge']);
        Route::post('/logout', [LoginController::class, 'logout']);


        // Orders
        Route::post('/create-order', [OrderController::class, 'store']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{orderId}', [OrderController::class, 'show'])->whereNumber('orderId');
        // Checkout
        Route::match(['get', 'post'], '/orders/{order}/checkout', [
            CheckoutController::class,
            'checkoutPayment',
        ])->whereNumber('order');

        // Success page: verify with Stripe API (works without local webhook)
        Route::post('/checkout/confirm', [CheckoutController::class, 'confirm']);
        Route::get('/orders/{order}/payment-status', [CheckoutController::class, 'paymentStatus'])
            ->whereNumber('order');
    });
});
