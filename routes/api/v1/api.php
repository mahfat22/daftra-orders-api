<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh', [AuthController::class, 'refresh']);
});

Route::middleware(['auth:api'])->group(function () {

    Route::prefix('auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    Route::apiResource('orders', OrderController::class)->names([
        'index' => 'orders.index',
        'store' => 'orders.store',
        'show' => 'orders.show',
        'update' => 'orders.update',
        'destroy' => 'orders.destroy',
    ]);
    Route::post('orders/{id}/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');
    Route::post('orders/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    Route::apiResource('payments', PaymentController::class)->only(['index', 'show', 'store'])->names([
        'index' => 'payments.index',
        'store' => 'payments.store',
        'show' => 'payments.show',
    ]);
    Route::post('payments/process', [PaymentController::class, 'store'])->name('payments.process');
    Route::get('orders/{orderId}/payments', [PaymentController::class, 'orderPayments'])->name('orders.payments');

    Route::get('payment-methods', [PaymentController::class, 'paymentMethods'])->name('payments.methods');
});
