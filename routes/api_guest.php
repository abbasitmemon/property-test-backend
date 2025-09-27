<?php

use App\Http\Controllers\Guest\BookingController;
use App\Http\Controllers\Guest\PropertyController;
use Illuminate\Support\Facades\Route;

Route::prefix('guest')->group(function () {
    // Properties Module
    Route::get('properties', [PropertyController::class, 'index']);
    Route::get('properties/{property}', [PropertyController::class, 'show']);
    // Booking Module
    Route::post('bookings', [BookingController::class, 'store']);
});
