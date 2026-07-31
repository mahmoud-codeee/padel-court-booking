<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['min_hours', 'max_hours', 'price_per_hour', 'is_active'])]
class DiscountTier extends Model
{
    protected function casts(): array
    {
        return [
            'min_hours' => 'integer',
            'max_hours' => 'integer',
            'price_per_hour' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function coversHours(int $hours): bool
    {
        if ($hours < $this->min_hours) {
            return false;
        }

        return $this->max_hours === null || $hours <= $this->max_hours;
    }
}
