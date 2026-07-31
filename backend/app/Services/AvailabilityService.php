<?php

namespace App\Services;

use App\Models\BookingSlot;
use App\Models\Court;
use App\Models\CourtClosure;
use App\Models\CourtWorkingHour;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilityService
{
    private const int MIN_HOUR = 0;

    private const int MAX_HOUR = 23;

    /**
     * @return array<int, bool> hour (0-23) => is at least one court free for that hour
     */
    public function getAvailableSlotsForDate(Carbon $date): array
    {
        return collect($this->getFreeCourtsPerHourForDate($date))
            ->map(fn (array $courtIds) => count($courtIds) > 0)
            ->all();
    }

    /**
     * @return array<int, array<int>> hour (0-23) => list of free court ids
     */
    public function getFreeCourtsPerHourForDate(Carbon $date): array
    {
        $courtIds = Court::query()->where('is_active', true)->pluck('id');

        $workingHours = CourtWorkingHour::query()
            ->whereIn('court_id', $courtIds)
            ->where('day_of_week', $date->dayOfWeek)
            ->get()
            ->keyBy('court_id');

        $closures = CourtClosure::query()
            ->where('closure_date', $date->toDateString())
            ->where(fn ($q) => $q->whereNull('court_id')->orWhereIn('court_id', $courtIds))
            ->get();

        $occupied = BookingSlot::query()
            ->whereIn('court_id', $courtIds)
            ->where('slot_date', $date->toDateString())
            ->whereHas('booking', fn ($q) => $q->whereIn('status', ['pending', 'confirmed']))
            ->get(['court_id', 'slot_hour'])
            ->groupBy('court_id')
            ->map(fn (Collection $rows) => $rows->pluck('slot_hour')->all());

        $isToday = $date->isToday();
        $currentHour = now()->hour;

        $result = [];
        for ($hour = self::MIN_HOUR; $hour <= self::MAX_HOUR; $hour++) {
            if ($date->isPast() && ! $isToday) {
                $result[$hour] = [];

                continue;
            }
            if ($isToday && $hour <= $currentHour) {
                $result[$hour] = [];

                continue;
            }

            $result[$hour] = $courtIds
                ->filter(fn (int $courtId) => $this->isCourtFreeAtHour($courtId, $hour, $workingHours, $closures, $occupied))
                ->values()
                ->all();
        }

        return $result;
    }

    /**
     * @param  array<array{date: string, hour: int}>  $dateHourPairs
     * @return array<string, array<int>> "Y-m-d|hour" => list of free court ids
     */
    public function getFreeCourtsForRequestedSlots(array $dateHourPairs): array
    {
        $result = [];

        foreach (collect($dateHourPairs)->groupBy('date') as $date => $items) {
            $freeCourtsPerHour = $this->getFreeCourtsPerHourForDate(Carbon::parse($date));

            foreach ($items as $item) {
                $result["{$date}|{$item['hour']}"] = $freeCourtsPerHour[$item['hour']] ?? [];
            }
        }

        return $result;
    }

    private function isCourtFreeAtHour(
        int $courtId,
        int $hour,
        Collection $workingHoursByCourtId,
        Collection $closures,
        Collection $occupiedHoursByCourtId,
    ): bool {
        $workingHour = $workingHoursByCourtId->get($courtId);
        if (! $workingHour || $workingHour->is_closed) {
            return false;
        }
        if (! $this->slotWithinWindow($hour, $workingHour->open_time, $workingHour->close_time)) {
            return false;
        }

        foreach ($closures as $closure) {
            if ($closure->court_id !== null && $closure->court_id !== $courtId) {
                continue;
            }
            if ($closure->isFullDay()) {
                return false;
            }
            if ($this->slotOverlapsWindow($hour, $closure->start_time, $closure->end_time)) {
                return false;
            }
        }

        if (in_array($hour, $occupiedHoursByCourtId->get($courtId, []), true)) {
            return false;
        }

        return true;
    }

    /** Slot [hour, hour+1) must sit entirely within [windowStart, windowEnd). */
    private function slotWithinWindow(int $hour, ?string $windowStart, ?string $windowEnd): bool
    {
        if ($windowStart === null || $windowEnd === null) {
            return false;
        }

        return $this->hourStart($hour) >= $windowStart && $this->hourEnd($hour) <= $windowEnd;
    }

    /** Slot [hour, hour+1) overlaps at all with [windowStart, windowEnd). */
    private function slotOverlapsWindow(int $hour, ?string $windowStart, ?string $windowEnd): bool
    {
        return $this->hourStart($hour) < $windowEnd && $this->hourEnd($hour) > $windowStart;
    }

    private function hourStart(int $hour): string
    {
        return sprintf('%02d:00:00', $hour);
    }

    private function hourEnd(int $hour): string
    {
        return sprintf('%02d:00:00', $hour + 1);
    }
}
