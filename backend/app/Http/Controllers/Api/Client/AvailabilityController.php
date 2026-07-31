<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function __construct(private readonly AvailabilityService $availability) {}

    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $date = Carbon::parse($validated['date']);

        $slots = collect($this->availability->getAvailableSlotsForDate($date))
            ->map(fn (bool $available, int $hour) => ['hour' => $hour, 'available' => $available])
            ->values();

        return response()->json(['data' => $slots]);
    }
}
