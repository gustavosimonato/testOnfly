<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Travel\TravelOrderController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::delete('/logout', [AuthController::class, 'logout']);

    Route::get('/travel-orders', [TravelOrderController::class, 'index']);
    Route::post('/travel-orders', [TravelOrderController::class, 'store']);
    Route::get('/travel-orders/{id}', [TravelOrderController::class, 'show']);
    Route::patch('/travel-orders/{id}/status', [TravelOrderController::class, 'updateStatus']);
    Route::patch('/travel-orders/{id}/cancel', [TravelOrderController::class, 'cancel']);

});
