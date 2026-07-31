<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['batch_id', 'court_id', 'closure_date', 'start_time', 'end_time', 'reason', 'created_by'])]
class CourtClosure extends Model
{
    protected function casts(): array
    {
        return [
            'closure_date' => 'date',
        ];
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function isFullDay(): bool
    {
        return $this->start_time === null && $this->end_time === null;
    }
}
