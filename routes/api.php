<?php
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
});
Route::middleware('auth:api')->group(function () {
    // Order Endpoints
    Route::apiResource('orders', OrderController::class);

    // Payment Endpoints
    Route::get('payments', [PaymentController::class, 'index']);
    Route::post('payments/process', [PaymentController::class, 'process']);
});
