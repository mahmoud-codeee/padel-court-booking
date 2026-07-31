<?php

namespace App\Http\Requests\Admin;

use App\Models\DiscountTier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateDiscountTierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'min_hours' => ['sometimes', 'required', 'integer', 'min:1'],
            'max_hours' => ['nullable', 'integer', 'gte:min_hours'],
            'price_per_hour' => ['sometimes', 'required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var DiscountTier $tier */
            $tier = $this->route('tier');

            $min = (int) $this->input('min_hours', $tier->min_hours);
            $max = $this->has('max_hours')
                ? ($this->filled('max_hours') ? (int) $this->input('max_hours') : null)
                : $tier->max_hours;

            $overlaps = DiscountTier::query()
                ->where('id', '!=', $tier->id)
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
