<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Client-facing view of a booking slot. Deliberately excludes court_id/name —
 * court identity is never revealed to the client, before or after booking.
 */
class BookingSlotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'date' => $this->slot_date->toDateString(),
            'hour' => $this->slot_hour,
        ];
    }
}
