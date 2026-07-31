<?php

namespace App\Services;

use App\Models\DiscountTier;
use App\Models\PricingSetting;

class PricingService
{
    public function getSettings(): PricingSetting
    {
        return PricingSetting::query()->findOrFail(1);
    }

    public function updateBasePrice(float $basePricePerHour): PricingSetting
    {
        $settings = $this->getSettings();
        $settings->update(['base_price_per_hour' => $basePricePerHour]);

        return $settings->fresh();
    }

    /**
     * The per-hour rate that applies for a booking totalling $totalHours hours.
     * Falls back to the base price if no discount tier covers this hour count.
     */
    public function pricePerHourFor(int $totalHours): float
    {
        $tier = DiscountTier::query()
            ->where('is_active', true)
            ->where('min_hours', '<=', $totalHours)
            ->where(fn ($q) => $q->whereNull('max_hours')->orWhere('max_hours', '>=', $totalHours))
            ->orderByDesc('min_hours')
            ->first();

        return (float) ($tier->price_per_hour ?? $this->getSettings()->base_price_per_hour);
    }

    /**
     * @return array{price_per_hour: float, total_amount: float, currency: string}
     */
    public function calculateTotal(int $totalHours): array
    {
        $pricePerHour = $this->pricePerHourFor($totalHours);

        return [
            'price_per_hour' => $pricePerHour,
            'total_amount' => round($pricePerHour * $totalHours, 2),
            'currency' => $this->getSettings()->currency,
        ];
    }
}
