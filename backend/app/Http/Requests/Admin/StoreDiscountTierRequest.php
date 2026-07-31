<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDiscountTierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'min_hours' => ['required', 'integer', 'min:1'],
            'max_hours' => ['nullable', 'integer', 'gte:min_hours'],
            'price_per_hour' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $min = (int) $this->input('min_hours');
            $max = $this->filled('max_hours') ? (int) $this->input('max_hours') : null;

            $overlaps = \App\Models\DiscountTier::query()
                ->where('id', '!=', $this->route('tier')?->id ?? 0)
                ->where(function ($q) use ($min, $max) {
                    $q->where('min_hours', '<=', $max ?? PHP_INT_MAX)
                        ->where(fn ($q2) => $q2->whereNull('max_hours')->orWhere('max_hours', '>=', $min));
                })
                ->exists();

            if ($overlaps) {
                $validator->errors()->add('min_hours', 'This hour range overlaps with an existing discount tier.');
            }
        });
    }
}
