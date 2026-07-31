<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;
use App\Enums\PaymentMethod;

class StoreBookingRequest extends FormRequest
{
    private const int MAX_ADVANCE_BOOKING_DAYS = 30;

    private const int MAX_SLOTS_PER_BOOKING = 40;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxDate = now()->addDays(self::MAX_ADVANCE_BOOKING_DAYS)->toDateString();

        return [
            'slots' => ['required', 'array', 'min:1', 'max:'.self::MAX_SLOTS_PER_BOOKING],
            'slots.*.date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:'.$maxDate],
            'slots.*.hour' => ['required', 'integer', 'between:0,23'],
            'customer' => ['required', 'array'],
            'customer.phone' => ['required', 'string', 'regex:/^[0-9+\s\-]{6,20}$/'],
            'customer.name' => ['nullable', 'string', 'max:100'],
            'customer.email' => ['nullable', 'email', 'max:150'],
            'payment_method' => ['required', new Enum(PaymentMethod::class)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $slots = $this->input('slots', []);
            $keys = array_map(fn ($s) => ($s['date'] ?? '').'|'.($s['hour'] ?? ''), $slots);

            if (count($keys) !== count(array_unique($keys))) {
                $validator->errors()->add('slots', 'Duplicate slots were selected.');
            }
        });
    }
}
