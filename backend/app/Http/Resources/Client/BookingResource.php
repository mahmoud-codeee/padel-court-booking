<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Client-facing booking view. Explicit whitelist — never include court_id/name
 * (see BookingSlotResource) or any other internal/admin-only field.
 */
class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'reference' => $this->reference,
            'status' => $this->status->value,
            'payment_method' => $this->payment_method->value,
            'payment_status' => $this->payment_status->value,
            'total_hours' => $this->total_hours,
            'price_per_hour' => (float) $this->price_per_hour_applied,
            'total_amount' => (float) $this->total_amount,
            'currency' => $this->currency,
            'customer_phone' => $this->customer_phone,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'slots' => BookingSlotResource::collection($this->whenLoaded('slots')),
            'payment_checkout_url' => $this->whenLoaded('payment', fn () => $this->payment?->thawani_checkout_url),
            'created_at' => $this->created_at,
        ];
    }
}
