<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePricingSettingRequest;
use App\Http\Resources\PricingSettingResource;
use App\Services\PricingService;

class PricingSettingController extends Controller
{
    public function __construct(private readonly PricingService $pricing) {}

    public function show(): PricingSettingResource
    {
        return new PricingSettingResource($this->pricing->getSettings());
    }

    public function update(UpdatePricingSettingRequest $request): PricingSettingResource
    {
        $settings = $this->pricing->updateBasePrice((float) $request->validated('base_price_per_hour'));

        return new PricingSettingResource($settings);
    }
}
