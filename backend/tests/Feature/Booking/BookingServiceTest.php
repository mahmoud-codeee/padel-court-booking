<?php

namespace Tests\Feature\Booking;

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Exceptions\SlotUnavailableException;
use App\Models\Court;
use App\Models\CourtClosure;
use App\Models\DiscountTier;
use App\Models\PricingSetting;
use App\Services\Booking\BookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    private BookingService $bookings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bookings = app(BookingService::class);

        PricingSetting::query()->create(['base_price_per_hour' => 5, 'currency' => 'OMR']);
        DiscountTier::query()->insert([
            ['min_hours' => 1, 'max_hours' => 1, 'price_per_hour' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['min_hours' => 2, 'max_hours' => null, 'price_per_hour' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function createCourt(string $name = 'Court'): Court
    {
        $court = Court::query()->create(['name' => $name, 'is_active' => true]);

        $rows = [];
        for ($day = 0; $day <= 6; $day++) {
            $rows[] = [
                'court_id' => $court->id,
                'day_of_week' => $day,
                'is_closed' => false,
                'open_time' => '08:00:00',
                'close_time' => '23:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        $court->workingHours()->insert($rows);

        return $court;
    }

    #[Test]
    public function cash_booking_is_confirmed_immediately_with_tiered_pricing_applied(): void
    {
        $this->createCourt();
        $date = Carbon::tomorrow()->toDateString();

        $booking = $this->bookings->createBooking(
            [['date' => $date, 'hour' => 10], ['date' => $date, 'hour' => 11]],
            ['phone' => '+96891234567'],
            PaymentMethod::Cash,
        );

        $this->assertSame(BookingStatus::Confirmed, $booking->status);
        $this->assertSame(PaymentStatus::Unpaid, $booking->payment_status);
        $this->assertNull($booking->hold_expires_at);
        $this->assertSame(2, $booking->total_hours);
        $this->assertEquals(4.00, (float) $booking->price_per_hour_applied); // 2hr tier rate
        $this->assertEquals(8.00, (float) $booking->total_amount);
        $this->assertCount(2, $booking->slots);
    }

    #[Test]
    public function online_booking_stays_pending_with_a_hold_expiry(): void
    {
        $this->createCourt();
        $date = Carbon::tomorrow()->toDateString();

        $booking = $this->bookings->createBooking(
            [['date' => $date, 'hour' => 10]],
            ['phone' => '+96891234567'],
            PaymentMethod::Online,
        );

        $this->assertSame(BookingStatus::Pending, $booking->status);
        $this->assertSame(PaymentStatus::AwaitingPayment, $booking->payment_status);
        $this->assertNotNull($booking->hold_expires_at);
        $this->assertTrue($booking->hold_expires_at->isFuture());
    }

    #[Test]
    public function booking_a_slot_outside_working_hours_is_rejected(): void
    {
        $court = $this->createCourt();
        $court->workingHours()->where('day_of_week', Carbon::tomorrow()->dayOfWeek)->update(['is_closed' => true]);

        $this->expectException(SlotUnavailableException::class);

        $this->bookings->createBooking(
            [['date' => Carbon::tomorrow()->toDateString(), 'hour' => 10]],
            ['phone' => '+96891234567'],
            PaymentMethod::Cash,
        );
    }

    #[Test]
    public function booking_a_slot_covered_by_a_full_day_closure_is_rejected(): void
    {
        $this->createCourt();
        $date = Carbon::tomorrow();

        CourtClosure::query()->create([
            'batch_id' => (string) \Illuminate\Support\Str::uuid(),
            'court_id' => null, // all courts
            'closure_date' => $date->toDateString(),
            'created_by' => \App\Models\Admin::query()->create([
                'name' => 'Admin', 'email' => 'a@a.com', 'password' => 'secret',
            ])->id,
        ]);

        $this->expectException(SlotUnavailableException::class);

        $this->bookings->createBooking(
            [['date' => $date->toDateString(), 'hour' => 10]],
            ['phone' => '+96891234567'],
            PaymentMethod::Cash,
        );
    }

    #[Test]
    public function once_all_courts_are_booked_for_an_hour_further_bookings_for_that_hour_are_rejected(): void
    {
        $this->createCourt('A');
        $this->createCourt('B');
        $date = Carbon::tomorrow()->toDateString();

        // Two courts total — book the hour out twice, third attempt must fail.
        $this->bookings->createBooking([['date' => $date, 'hour' => 10]], ['phone' => '1'], PaymentMethod::Cash);
        $this->bookings->createBooking([['date' => $date, 'hour' => 10]], ['phone' => '2'], PaymentMethod::Cash);

        $this->expectException(SlotUnavailableException::class);
        $this->bookings->createBooking([['date' => $date, 'hour' => 10]], ['phone' => '3'], PaymentMethod::Cash);
    }

    #[Test]
    public function release_slots_frees_the_hour_for_future_bookings(): void
    {
        $this->createCourt();
        $date = Carbon::tomorrow()->toDateString();

        $booking = $this->bookings->createBooking([['date' => $date, 'hour' => 10]], ['phone' => '1'], PaymentMethod::Cash);
        $this->bookings->releaseSlots($booking, BookingStatus::Cancelled);

        $this->assertSame(BookingStatus::Cancelled, $booking->fresh()->status);
        $this->assertCount(0, $booking->fresh()->slots);

        // Should now be re-bookable.
        $rebooked = $this->bookings->createBooking([['date' => $date, 'hour' => 10]], ['phone' => '2'], PaymentMethod::Cash);
        $this->assertSame(BookingStatus::Confirmed, $rebooked->status);
    }
}
