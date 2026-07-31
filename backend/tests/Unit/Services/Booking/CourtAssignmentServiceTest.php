<?php

namespace Tests\Unit\Services\Booking;

use App\Exceptions\SlotUnavailableException;
use App\Services\Booking\CourtAssignmentService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CourtAssignmentServiceTest extends TestCase
{
    private CourtAssignmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CourtAssignmentService;
    }

    #[Test]
    public function it_assigns_the_same_court_across_a_contiguous_run_when_one_court_spans_it(): void
    {
        $slots = [
            ['date' => '2026-09-01', 'hour' => 10],
            ['date' => '2026-09-01', 'hour' => 11],
            ['date' => '2026-09-01', 'hour' => 12],
        ];

        // Court 1 is free the whole run; court 2 only free at hour 11.
        $free = [
            '2026-09-01|10' => [1],
            '2026-09-01|11' => [1, 2],
            '2026-09-01|12' => [1],
        ];

        $assignments = $this->service->assign($slots, $free);

        $courtIds = array_unique(array_column($assignments, 'court_id'));
        $this->assertCount(1, $courtIds, 'All contiguous hours should land on the single court that spans the whole run');
        $this->assertSame(1, $courtIds[array_key_first($courtIds)]);
    }

    #[Test]
    public function it_falls_back_to_independent_per_hour_assignment_when_no_single_court_spans_the_run(): void
    {
        $slots = [
            ['date' => '2026-09-01', 'hour' => 10],
            ['date' => '2026-09-01', 'hour' => 11],
        ];

        // No court is free for both hours — court 1 only at 10, court 2 only at 11.
        $free = [
            '2026-09-01|10' => [1],
            '2026-09-01|11' => [2],
        ];

        $assignments = $this->service->assign($slots, $free);

        $byHour = collect($assignments)->keyBy('hour');
        $this->assertSame(1, $byHour[10]['court_id']);
        $this->assertSame(2, $byHour[11]['court_id']);
    }

    #[Test]
    public function it_splits_non_contiguous_hours_into_separate_runs(): void
    {
        $slots = [
            ['date' => '2026-09-01', 'hour' => 9],
            ['date' => '2026-09-01', 'hour' => 10],
            ['date' => '2026-09-01', 'hour' => 15],
        ];

        $free = [
            '2026-09-01|9' => [1, 2],
            '2026-09-01|10' => [1, 2],
            '2026-09-01|15' => [1, 2],
        ];

        $assignments = $this->service->assign($slots, $free);
        $byHour = collect($assignments)->keyBy('hour');

        // The 9-10 run must share a court; the isolated 15 may differ.
        $this->assertSame($byHour[9]['court_id'], $byHour[10]['court_id']);
    }

    #[Test]
    public function it_throws_when_a_requested_slot_has_no_free_courts(): void
    {
        $this->expectException(SlotUnavailableException::class);

        $slots = [
            ['date' => '2026-09-01', 'hour' => 10],
        ];
        $free = [
            '2026-09-01|10' => [],
        ];

        $this->service->assign($slots, $free);
    }

    #[Test]
    public function it_reports_only_the_conflicting_slots_in_the_exception(): void
    {
        $slots = [
            ['date' => '2026-09-01', 'hour' => 10],
            ['date' => '2026-09-01', 'hour' => 11],
        ];
        $free = [
            '2026-09-01|10' => [1],
            '2026-09-01|11' => [],
        ];

        try {
            $this->service->assign($slots, $free);
            $this->fail('Expected SlotUnavailableException was not thrown.');
        } catch (SlotUnavailableException $e) {
            $response = $e->render(request());
            $data = $response->getData(true);
            $this->assertCount(1, $data['conflicting_slots']);
            $this->assertSame(11, $data['conflicting_slots'][0]['hour']);
        }
    }
}
