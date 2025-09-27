<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AvailabilityController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\PropertyController;

/*
|--------------------------------------------------------------------------
| Admin API Routes (in routes/api_admin.php)
|--------------------------------------------------------------------------
|
| 1. Public admin login  — needs no token yet.
| 2. All other admin routes — protected by Sanctum + AdminMiddleware.
|
*/

Route::prefix('admin')->group(function () {

    Route::post('login', [AuthController::class, 'login']);
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        // Logout
        Route::post('logout', [AuthController::class, 'logout']);

        // Properties Module
        Route::get('properties', [PropertyController::class, 'index']);
        Route::post('properties', [PropertyController::class, 'store']);
        Route::put('properties/{property}', [PropertyController::class, 'update']);
        Route::get('properties/{property}', [PropertyController::class, 'show']);
        Route::delete('properties/{property}', [PropertyController::class, 'destroy']);

        // Availability Module
        Route::get('availability/{property_id}', [AvailabilityController::class, 'index']);
        Route::post('availability', [AvailabilityController::class, 'store']);

        // Booking Module
        Route::get('bookings',      [BookingController::class, 'index']);
        Route::patch('bookings/{booking}/status', [BookingController::class, 'updateStatus']);
    });
});
