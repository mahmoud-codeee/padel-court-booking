<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourtClosureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'batch_id' => $this->batch_id,
            'court_id' => $this->court_id,
            'court_name' => $this->court_id === null ? 'All courts' : $this->court?->name,
            'closure_date' => $this->closure_date->toDateString(),
            'start_time' => $this->start_time !== null ? substr($this->start_time, 0, 5) : null,
            'end_time' => $this->end_time !== null ? substr($this->end_time, 0, 5) : null,
            'is_full_day' => $this->isFullDay(),
            'reason' => $this->reason,
        ];
    }
}
