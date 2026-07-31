<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateWorkingHoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hours' => ['required', 'array', 'size:7'],
            'hours.*.day_of_week' => ['required', 'integer', 'between:0,6', 'distinct'],
            'hours.*.is_closed' => ['required', 'boolean'],
            'hours.*.open_time' => ['nullable', 'date_format:H:i'],
            'hours.*.close_time' => ['nullable', 'date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ($this->input('hours', []) as $index => $hour) {
                $isClosed = filter_var($hour['is_closed'] ?? false, FILTER_VALIDATE_BOOLEAN);
                if (! $isClosed && (empty($hour['open_time']) || empty($hour['close_time']))) {
                    $validator->errors()->add("hours.$index.open_time", 'Open and close time are required unless the day is marked closed.');
                }
                if (! $isClosed && ! empty($hour['open_time']) && ! empty($hour['close_time']) && $hour['close_time'] <= $hour['open_time']) {
                    $validator->errors()->add("hours.$index.close_time", 'Close time must be after open time.');
                }
            }
        });
    }
}
