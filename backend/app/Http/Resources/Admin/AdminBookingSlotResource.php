<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminBookingSlotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'date' => $this->slot_date->toDateString(),
            'hour' => $this->slot_hour,
            'court_id' => $this->court_id,
            'court_name' => $this->whenLoaded('court', fn () => $this->court->name),
        ];
    }
}
