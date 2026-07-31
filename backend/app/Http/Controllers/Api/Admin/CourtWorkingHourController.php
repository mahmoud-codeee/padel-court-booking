<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateWorkingHoursRequest;
use App\Http\Resources\CourtWorkingHourResource;
use App\Models\Court;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourtWorkingHourController extends Controller
{
    public function update(UpdateWorkingHoursRequest $request, Court $court): AnonymousResourceCollection
    {
        foreach ($request->validated('hours') as $hour) {
            $isClosed = (bool) $hour['is_closed'];

            $court->workingHours()->updateOrCreate(
                ['day_of_week' => $hour['day_of_week']],
                [
                    'is_closed' => $isClosed,
                    'open_time' => $isClosed ? null : $hour['open_time'],
                    'close_time' => $isClosed ? null : $hour['close_time'],
                ]
            );
        }

        return CourtWorkingHourResource::collection(
            $court->workingHours()->orderBy('day_of_week')->get()
        );
    }
}
