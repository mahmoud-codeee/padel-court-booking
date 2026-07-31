<?php

namespace App\Services\Booking;

use App\Exceptions\SlotUnavailableException;

class CourtAssignmentService
{
    /**
     * Assign a court to each requested (date, hour) slot. For a contiguous run of
     * hours on the same day, prefers assigning a single court across the whole
     * run (so the player doesn't switch courts mid-session), falling back to
     * independent random per-hour assignment when no single court covers it.
     *
     * @param  array<array{date: string, hour: int}>  $requestedSlots
     * @param  array<string, array<int>>  $freeCourtsByDateHour  keyed "date|hour"
     * @return array<array{date: string, hour: int, court_id: int}>
     *
     * @throws SlotUnavailableException if any requested slot has zero free courts
     */
    public function assign(array $requestedSlots, array $freeCourtsByDateHour): array
    {
        $unavailable = array_values(array_filter(
            $requestedSlots,
            fn (array $slot) => empty($freeCourtsByDateHour["{$slot['date']}|{$slot['hour']}"])
        ));

        if (! empty($unavailable)) {
            throw new SlotUnavailableException($unavailable);
        }

        $assignments = [];

        $byDate = [];
        foreach ($requestedSlots as $slot) {
            $byDate[$slot['date']][] = (int) $slot['hour'];
        }

        foreach ($byDate as $date => $hours) {
            sort($hours);
            foreach ($this->splitIntoContiguousRuns($hours) as $run) {
                $assignments = [...$assignments, ...$this->assignRun($date, $run, $freeCourtsByDateHour)];
            }
        }

        return $assignments;
    }

    /**
     * @param  array<int>  $run
     * @param  array<string, array<int>>  $freeCourtsByDateHour
     * @return array<array{date: string, hour: int, court_id: int}>
     */
    private function assignRun(string $date, array $run, array $freeCourtsByDateHour): array
    {
        $candidateSets = array_map(
            fn (int $hour) => $freeCourtsByDateHour["{$date}|{$hour}"],
            $run
        );

        $common = array_shift($candidateSets);
        foreach ($candidateSets as $set) {
            $common = array_intersect($common, $set);
        }

        if (! empty($common)) {
            $common = array_values($common);
            $courtId = $common[array_rand($common)];

            return array_map(
                fn (int $hour) => ['date' => $date, 'hour' => $hour, 'court_id' => $courtId],
                $run
            );
        }

        return array_map(function (int $hour) use ($date, $freeCourtsByDateHour) {
            $candidates = $freeCourtsByDateHour["{$date}|{$hour}"];

            return ['date' => $date, 'hour' => $hour, 'court_id' => $candidates[array_rand($candidates)]];
        }, $run);
    }

    /**
     * @param  array<int>  $sortedHours
     * @return array<array<int>>
     */
    private function splitIntoContiguousRuns(array $sortedHours): array
    {
        $runs = [];
        $current = [];

        foreach ($sortedHours as $hour) {
            if (empty($current) || $hour === end($current) + 1) {
                $current[] = $hour;
            } else {
                $runs[] = $current;
                $current = [$hour];
            }
        }
        if (! empty($current)) {
            $runs[] = $current;
        }

        return $runs;
    }
}
