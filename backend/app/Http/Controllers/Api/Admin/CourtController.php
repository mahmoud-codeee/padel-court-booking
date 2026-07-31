<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCourtRequest;
use App\Http\Requests\Admin\UpdateCourtRequest;
use App\Http\Resources\CourtResource;
use App\Models\Court;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourtController extends Controller
{
    private const array DEFAULT_WORKING_HOURS = ['open' => '08:00:00', 'close' => '23:00:00'];

    public function index(): AnonymousResourceCollection
    {
        $courts = Court::query()->with('workingHours')->orderBy('name')->get();

        return CourtResource::collection($courts);
    }

    public function store(StoreCourtRequest $request): JsonResponse
    {
        $court = Court::query()->create($request->validated());

        $workingHours = [];
        for ($day = 0; $day <= 6; $day++) {
            $workingHours[] = [
                'court_id' => $court->id,
                'day_of_week' => $day,
                'is_closed' => false,
                'open_time' => self::DEFAULT_WORKING_HOURS['open'],
                'close_time' => self::DEFAULT_WORKING_HOURS['close'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        $court->workingHours()->insert($workingHours);

        return (new CourtResource($court->load('workingHours')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Court $court): CourtResource
    {
        return new CourtResource($court->load('workingHours'));
    }

    public function update(UpdateCourtRequest $request, Court $court): CourtResource
    {
        $court->update($request->validated());

        return new CourtResource($court->load('workingHours'));
    }

    public function destroy(Court $court): JsonResponse
    {
        $hasFutureSlots = $court->bookingSlots()
            ->where('slot_date', '>=', now()->toDateString())
            ->whereHas('booking', fn ($q) => $q->whereIn('status', ['pending', 'confirmed']))
            ->exists();

        if ($hasFutureSlots) {
            return response()->json([
                'message' => 'This court has upcoming bookings and cannot be deleted. Deactivate it instead.',
            ], 409);
        }

        $court->delete();

        return response()->json(['message' => 'Court deleted.']);
    }
}
