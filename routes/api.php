<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DriverController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('driver')->group(function () {
        Route::get('/jobs', [DriverController::class, 'jobs']);
        Route::post('/scan-qr', [DriverController::class, 'scanQr']);
        Route::get('/delivery/{delivery}', [DriverController::class, 'deliveryDetails']);
        Route::post('/delivery/{delivery}/complete', [DriverController::class, 'complete']);
    });
});