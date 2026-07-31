<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'base_price_per_hour' => (float) $this->base_price_per_hour,
            'currency' => $this->currency,
        ];
    }
}
