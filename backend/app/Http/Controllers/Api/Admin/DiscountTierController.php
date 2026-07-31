<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDiscountTierRequest;
use App\Http\Requests\Admin\UpdateDiscountTierRequest;
use App\Http\Resources\DiscountTierResource;
use App\Models\DiscountTier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DiscountTierController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return DiscountTierResource::collection(
            DiscountTier::query()->orderBy('min_hours')->get()
        );
    }

    public function store(StoreDiscountTierRequest $request): JsonResponse
    {
        $tier = DiscountTier::query()->create($request->validated());

        return (new DiscountTierResource($tier))->response()->setStatusCode(201);
    }

    public function update(UpdateDiscountTierRequest $request, DiscountTier $tier): DiscountTierResource
    {
        $tier->update($request->validated());

        return new DiscountTierResource($tier);
    }

    public function destroy(DiscountTier $tier): JsonResponse
    {
        $tier->delete();

        return response()->json(['message' => 'Discount tier removed.']);
    }
}
