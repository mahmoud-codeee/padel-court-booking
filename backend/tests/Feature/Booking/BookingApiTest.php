<?php

namespace Tests\Feature\Booking;

use App\Models\Court;
use App\Models\DiscountTier;
use App\Models\PricingSetting;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        PricingSetting::query()->create(['base_price_per_hour' => 5, 'currency' => 'OMR']);
        DiscountTier::query()->create(['min_hours' => 1, 'max_hours' => null, 'price_per_hour' => 5, 'is_active' => true]);

        $court = Court::query()->create(['name' => 'Court 1', 'is_active' => true]);
        $rows = [];
        for ($day = 0; $day <= 6; $day++) {
            $rows[] = [
                'court_id' => $court->id, 'day_of_week' => $day, 'is_closed' => false,
                'open_time' => '08:00:00', 'close_time' => '23:00:00',
                'created_at' => now(), 'updated_at' => now(),
            ];
        }
        $court->workingHours()->insert($rows);
    }

    #[Test]
    public function booking_a_past_date_is_rejected_by_validation(): void
    {
        $response = $this->postJson('/api/bookings', [
            'slots' => [['date' => Carbon::yesterday()->toDateString(), 'hour' => 10]],
            'customer' => ['phone' => '+96891234567'],
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['slots.0.date']);
    }

    #[Test]
    public function a_cash_booking_can_be_created_and_retrieved_without_ever_exposing_court_identity(): void
    {
        $date = Carbon::tomorrow()->toDateString();

        $create = $this->postJson('/api/bookings', [
            'slots' => [['date' => $date, 'hour' => 10]],
            'customer' => ['phone' => '+96891234567', 'name' => 'Jane'],
            'payment_method' => 'cash',
        ]);

        $create->assertStatus(201)
            ->assertJsonPath('data.status', 'confirmed')
            ->assertJsonPath('data.payment_method', 'cash');

        $reference = $create->json('data.reference');
        $raw = $create->getContent();
        $this->assertStringNotContainsString('court_id', $raw);
        $this->assertStringNotContainsString('court_name', $raw);
        $this->assertStringNotContainsString('"Court 1"', $raw);

        $show = $this->getJson("/api/bookings/{$reference}");
        $show->assertStatus(200)->assertJsonPath('data.reference', $reference);
        $this->assertStringNotContainsString('court_id', $show->getContent());
    }

    #[Test]
    public function booking_the_same_full_hour_twice_returns_a_409_conflict(): void
    {
        $date = Carbon::tomorrow()->toDateString();
        $payload = fn (string $phone) => [
            'slots' => [['date' => $date, 'hour' => 14]],
            'customer' => ['phone' => $phone],
            'payment_method' => 'cash',
        ];

        $this->postJson('/api/bookings', $payload('+96891111111'))->assertStatus(201);

        // Only one court seeded in setUp(), so the second request for the same hour must conflict.
        $second = $this->postJson('/api/bookings', $payload('+96892222222'));
        $second->assertStatus(409)->assertJsonStructure(['message', 'conflicting_slots']);
    }

    #[Test]
    public function availability_endpoint_reflects_bookings(): void
    {
        $date = Carbon::tomorrow()->toDateString();

        $before = $this->getJson("/api/availability?date={$date}");
        $before->assertStatus(200);
        $hourEntry = collect($before->json('data'))->firstWhere('hour', 16);
        $this->assertTrue($hourEntry['available']);

        $this->postJson('/api/bookings', [
            'slots' => [['date' => $date, 'hour' => 16]],
            'customer' => ['phone' => '+96891234567'],
            'payment_method' => 'cash',
        ])->assertStatus(201);

        $after = $this->getJson("/api/availability?date={$date}");
        $hourEntryAfter = collect($after->json('data'))->firstWhere('hour', 16);
        $this->assertFalse($hourEntryAfter['available']);
    }
}
