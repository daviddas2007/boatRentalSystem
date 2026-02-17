<?php

use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BoatController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/profile', [AuthController::class, 'updateProfile']);
    });
});

Route::get('/boats', [BoatController::class, 'index']);
Route::get('/boats/types', [BoatController::class, 'types']);
Route::get('/boats/{slug}', [BoatController::class, 'show']);
Route::get('/boats/{boat}/availability', [BoatController::class, 'getAvailability']);
Route::get('/boats/{boatId}/reviews', [ReviewController::class, 'boatReviews']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/boats', [BoatController::class, 'store']);
    Route::put('/boats/{boat}', [BoatController::class, 'update']);
    Route::delete('/boats/{boat}', [BoatController::class, 'destroy']);
    Route::post('/boats/{boat}/images', [BoatController::class, 'uploadImages']);
    Route::post('/boats/{boat}/availability', [BoatController::class, 'setAvailability']);
    Route::get('/my-boats', [BoatController::class, 'myBoats']);

    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    Route::post('/bookings/{booking}/confirm', [BookingController::class, 'confirm']);
    Route::post('/bookings/{booking}/complete', [BookingController::class, 'complete']);

    Route::post('/payments/create-intent', [PaymentController::class, 'createIntent']);
    Route::post('/payments/confirm', [PaymentController::class, 'confirm']);
    Route::get('/payments/{payment}/invoice', [PaymentController::class, 'invoice']);

    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);

    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/stats', [AdminDashboardController::class, 'stats']);
        Route::get('/popular-boats', [AdminDashboardController::class, 'popularBoats']);
        Route::get('/monthly-revenue', [AdminDashboardController::class, 'monthlyRevenue']);
        Route::get('/recent-bookings', [AdminDashboardController::class, 'recentBookings']);
        Route::get('/bookings-by-status', [AdminDashboardController::class, 'bookingsByStatus']);
        Route::get('/revenue-by-type', [AdminDashboardController::class, 'revenueByBoatType']);
        Route::get('/bookings', [BookingController::class, 'adminIndex']);
        Route::post('/payments/refund', [PaymentController::class, 'refund']);
    });
});
