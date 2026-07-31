<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'booking_id', 'provider', 'client_reference_id', 'thawani_session_id',
    'thawani_checkout_url', 'thawani_status', 'amount', 'currency',
    'raw_session_response', 'raw_webhook_payload', 'paid_at',
])]
class Payment extends Model
{
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'raw_session_response' => 'array',
            'raw_webhook_payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
