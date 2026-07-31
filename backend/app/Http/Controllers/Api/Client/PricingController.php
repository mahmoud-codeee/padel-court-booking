<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\DiscountTierResource;
use App\Models\DiscountTier;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;

class PricingController extends Controller
{
    public function __construct(private readonly PricingService $pricing) {}

    public function show(): JsonResponse
    {
        $settings = $this->pricing->getSettings();

        return response()->json([
            'data' => [
                'base_price_per_hour' => (float) $settings->base_price_per_hour,
                'currency' => $settings->currency,
                'discount_tiers' => DiscountTierResource::collection(
                    DiscountTier::query()->where('is_active', true)->orderBy('min_hours')->get()
                ),
            ],
        ]);
    }
}
