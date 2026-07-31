<?php

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\CourtClosureController;
use App\Http\Controllers\Api\Admin\CourtController;
use App\Http\Controllers\Api\Admin\CourtWorkingHourController;
use App\Http\Controllers\Api\Admin\DiscountTierController;
use App\Http\Controllers\Api\Admin\PricingSettingController;
use Illuminate\Support\Facades\Route;

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
    });
});
