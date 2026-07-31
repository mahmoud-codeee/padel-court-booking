<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'status' => $this->status->value,
            'payment_method' => $this->payment_method->value,
            'payment_status' => $this->payment_status->value,
            'customer_phone' => $this->customer_phone,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'total_hours' => $this->total_hours,
            'price_per_hour' => (float) $this->price_per_hour_applied,
            'total_amount' => (float) $this->total_amount,
            'currency' => $this->currency,
            'admin_notes' => $this->admin_notes,
            'slots' => AdminBookingSlotResource::collection($this->whenLoaded('slots')),
            'hold_expires_at' => $this->hold_expires_at,
            'confirmed_at' => $this->confirmed_at,
            'cancelled_at' => $this->cancelled_at,
            'created_at' => $this->created_at,
        ];
    }
}
