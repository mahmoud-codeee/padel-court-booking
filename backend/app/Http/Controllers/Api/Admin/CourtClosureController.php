<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCourtClosureRequest;
use App\Http\Resources\CourtClosureResource;
use App\Models\CourtClosure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class CourtClosureController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $closures = CourtClosure::query()
            ->with('court')
            ->where('closure_date', '>=', now()->toDateString())
            ->orderBy('closure_date')
            ->get();

        return CourtClosureResource::collection($closures);
    }

    public function store(StoreCourtClosureRequest $request): JsonResponse
    {
        $batchId = (string) Str::uuid();
        $courtIds = $request->boolean('all_courts') ? [null] : $request->validated('court_ids');

        $rows = collect($courtIds)->map(fn (?int $courtId) => [
            'batch_id' => $batchId,
            'court_id' => $courtId,
            'closure_date' => $request->validated('closure_date'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'reason' => $request->input('reason'),
            'created_by' => $request->user()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        CourtClosure::query()->insert($rows);

        $created = CourtClosure::query()->with('court')->where('batch_id', $batchId)->get();

        return CourtClosureResource::collection($created)->response()->setStatusCode(201);
    }

    public function destroy(CourtClosure $closure): JsonResponse
    {
        $closure->delete();

        return response()->json(['message' => 'Closure removed.']);
    }
}
