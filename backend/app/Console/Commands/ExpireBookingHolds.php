<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Services\Booking\BookingService;
use Illuminate\Console\Command;

class ExpireBookingHolds extends Command
{
    protected $signature = 'bookings:expire-holds';

    protected $description = 'Expire pending online-payment bookings whose hold has lapsed, freeing their slots.';

    public function handle(BookingService $bookings): int
    {
        $expired = Booking::query()
            ->where('status', BookingStatus::Pending)
            ->whereNotNull('hold_expires_at')
            ->where('hold_expires_at', '<', now())
            ->get();

        foreach ($expired as $booking) {
            $bookings->releaseSlots($booking, BookingStatus::Expired);
        }

        $this->info("Expired {$expired->count()} booking hold(s).");

        return self::SUCCESS;
    }
}
