<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['booking_id', 'court_id', 'slot_date', 'slot_hour', 'price_per_hour_charged'])]
class BookingSlot extends Model
{
    protected function casts(): array
    {
        return [
            'slot_date' => 'date',
            'slot_hour' => 'integer',
            'price_per_hour_charged' => 'decimal:2',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }
}
