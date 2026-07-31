<?php

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Api\Admin\CourtClosureController;
use App\Http\Controllers\Api\Admin\CourtController;
use App\Http\Controllers\Api\Admin\CourtWorkingHourController;
use App\Http\Controllers\Api\Admin\DiscountTierController;
use App\Http\Controllers\Api\Admin\PricingSettingController;
use App\Http\Controllers\Api\Client\AvailabilityController;
use App\Http\Controllers\Api\Client\BookingController;
use App\Http\Controllers\Api\Client\PricingController;
use App\Http\Controllers\Api\Webhooks\ThawaniWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('availability', [AvailabilityController::class, 'show']);
Route::get('pricing', [PricingController::class, 'show']);
Route::post('bookings', [BookingController::class, 'store'])->middleware('throttle:20,1');
Route::get('bookings/{reference}', [BookingController::class, 'show']);
Route::get('bookings/{reference}/payment-status', [BookingController::class, 'paymentStatus']);
Route::post('webhooks/thawani', [ThawaniWebhookController::class, 'handle']);

Route::prefix('admin')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);

        Route::apiResource('courts', CourtController::class);
        Route::put('courts/{court}/working-hours', [CourtWorkingHourController::class, 'update']);

        Route::get('court-closures', [CourtClosureController::class, 'index']);
        Route::post('court-closures', [CourtClosureController::class, 'store']);
        Route::delete('court-closures/{closure}', [CourtClosureController::class, 'destroy']);

        Route::get('pricing-settings', [PricingSettingController::class, 'show']);
        Route::put('pricing-settings', [PricingSettingController::class, 'update']);

        Route::get('discount-tiers', [DiscountTierController::class, 'index']);
        Route::post('discount-tiers', [DiscountTierController::class, 'store']);
        Route::put('discount-tiers/{tier}', [DiscountTierController::class, 'update']);
        Route::delete('discount-tiers/{tier}', [DiscountTierController::class, 'destroy']);

        Route::get('bookings', [AdminBookingController::class, 'index']);
        Route::patch('bookings/{booking}/mark-paid', [AdminBookingController::class, 'markPaid']);
        Route::patch('bookings/{booking}/cancel', [AdminBookingController::class, 'cancel']);
    });
});
