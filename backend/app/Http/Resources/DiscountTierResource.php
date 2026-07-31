<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscountTierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'min_hours' => $this->min_hours,
            'max_hours' => $this->max_hours,
            'price_per_hour' => (float) $this->price_per_hour,
            'is_active' => $this->is_active,
        ];
    }
}
