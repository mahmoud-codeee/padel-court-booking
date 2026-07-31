<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[Fillable([
    'reference', 'customer_phone', 'customer_name', 'customer_email',
    'total_hours', 'price_per_hour_applied', 'total_amount', 'currency',
    'payment_method', 'status', 'payment_status', 'hold_expires_at',
    'confirmed_at', 'cancelled_at', 'admin_notes',
])]
class Booking extends Model
{
    protected static function booted(): void
    {
        static::creating(function (Booking $booking): void {
            $booking->reference ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'price_per_hour_applied' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
            'status' => BookingStatus::class,
            'payment_status' => PaymentStatus::class,
            'hold_expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function slots(): HasMany
    {
        return $this->hasMany(BookingSlot::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
