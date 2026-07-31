<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SlotUnavailableException extends Exception
{
    /**
     * @param  array<array{date: string, hour: int}>  $conflictingSlots
     */
    public function __construct(private readonly array $conflictingSlots)
    {
        parent::__construct('One or more selected slots are no longer available.');
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'conflicting_slots' => $this->conflictingSlots,
        ], 409);
    }
}
