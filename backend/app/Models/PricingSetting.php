<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['base_price_per_hour', 'currency'])]
class PricingSetting extends Model
{
    protected function casts(): array
    {
        return [
            'base_price_per_hour' => 'decimal:2',
        ];
    }
}
