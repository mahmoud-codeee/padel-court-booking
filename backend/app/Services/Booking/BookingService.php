<?php

namespace App\Services\Booking;

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Exceptions\SlotUnavailableException;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Court;
use App\Services\AvailabilityService;
use App\Services\PricingService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class BookingService
{
    private const int ONLINE_HOLD_MINUTES = 10;

    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly CourtAssignmentService $assignment,
        private readonly PricingService $pricing,
    ) {}

    /**
     * @param  array<array{date: string, hour: int}>  $requestedSlots
     * @param  array{phone: string, name?: ?string, email?: ?string}  $customer
     *
     * @throws SlotUnavailableException
     */
    public function createBooking(array $requestedSlots, array $customer, PaymentMethod $paymentMethod): Booking
    {
        $uniqueSlots = collect($requestedSlots)
            ->unique(fn (array $s) => "{$s['date']}|{$s['hour']}")
            ->values()
            ->all();

        $totalHours = count($uniqueSlots);
        $priceCalc = $this->pricing->calculateTotal($totalHours);
        $isOnline = $paymentMethod === PaymentMethod::Online;

        return DB::transaction(function () use ($uniqueSlots, $customer, $paymentMethod, $priceCalc, $totalHours, $isOnline) {
            // Lock all active courts to serialize concurrent booking-creation transactions
            // venue-wide — correct and simple for one venue's realistic booking volume.
            Court::query()->where('is_active', true)->lockForUpdate()->get();

            $freeCourtsByDateHour = $this->availability->getFreeCourtsForRequestedSlots($uniqueSlots);
            $assignments = $this->assignment->assign($uniqueSlots, $freeCourtsByDateHour);

            $booking = Booking::query()->create([
                'customer_phone' => $customer['phone'],
                'customer_name' => $customer['name'] ?? null,
                'customer_email' => $customer['email'] ?? null,
                'total_hours' => $totalHours,
                'price_per_hour_applied' => $priceCalc['price_per_hour'],
                'total_amount' => $priceCalc['total_amount'],
                'currency' => $priceCalc['currency'],
                'payment_method' => $paymentMethod,
                'status' => BookingStatus::Pending,
                'payment_status' => $isOnline ? PaymentStatus::AwaitingPayment : PaymentStatus::Unpaid,
                'hold_expires_at' => $isOnline ? now()->addMinutes(self::ONLINE_HOLD_MINUTES) : null,
            ]);

            $this->insertSlots($booking, $assignments, $priceCalc['price_per_hour'], $uniqueSlots);

            if (! $isOnline) {
                $booking->update(['status' => BookingStatus::Confirmed, 'confirmed_at' => now()]);
            }

            return $booking->fresh('slots');
        });
    }

    /**
     * Releases a booking's slots back to availability (used when an online-payment
     * hold expires, or a Thawani checkout-session creation call fails after the
     * booking rows were already committed).
     */
    public function releaseSlots(Booking $booking, BookingStatus $newStatus): void
    {
        DB::transaction(function () use ($booking, $newStatus) {
            $booking->slots()->delete();
            $booking->update([
                'status' => $newStatus,
                'cancelled_at' => $newStatus === BookingStatus::Cancelled ? now() : $booking->cancelled_at,
            ]);
        });
    }

    public function findByReference(string $reference): ?Booking
    {
        return Booking::query()->with('slots', 'payment')->where('reference', $reference)->first();
    }

    /**
     * @param  array<array{date: string, hour: int, court_id: int}>  $assignments
     * @param  array<array{date: string, hour: int}>  $requestedSlotsForErrorReporting
     */
    private function insertSlots(Booking $booking, array $assignments, float $pricePerHour, array $requestedSlotsForErrorReporting): void
    {
        $rows = array_map(fn (array $a) => [
            'booking_id' => $booking->id,
            'court_id' => $a['court_id'],
            'slot_date' => $a['date'],
            'slot_hour' => $a['hour'],
            'price_per_hour_charged' => $pricePerHour,
            'created_at' => now(),
            'updated_at' => now(),
        ], $assignments);

        try {
            BookingSlot::query()->insert($rows);
        } catch (QueryException $e) {
            if ((int) $e->getCode() === 23000) {
                throw new SlotUnavailableException($requestedSlotsForErrorReporting);
            }

            throw $e;
        }
    }
}
