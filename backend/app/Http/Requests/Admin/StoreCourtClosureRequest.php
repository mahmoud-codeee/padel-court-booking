<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCourtClosureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'court_ids' => ['nullable', 'array'],
            'court_ids.*' => ['integer', 'exists:courts,id'],
            'all_courts' => ['required', 'boolean'],
            'closure_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->boolean('all_courts') && empty($this->input('court_ids'))) {
                $validator->errors()->add('court_ids', 'Select at least one court, or mark this as an all-courts closure.');
            }

            $start = $this->input('start_time');
            $end = $this->input('end_time');
            if (($start && ! $end) || (! $start && $end)) {
                $validator->errors()->add('end_time', 'Start and end time must both be set, or both left empty for a full-day closure.');
            }
            if ($start && $end && $end <= $start) {
                $validator->errors()->add('end_time', 'End time must be after start time.');
            }
        });
    }
}
