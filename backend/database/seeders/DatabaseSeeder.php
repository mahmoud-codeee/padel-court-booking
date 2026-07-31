<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Court;
use App\Models\DiscountTier;
use App\Models\PricingSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Admin::query()->updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@padel.local')],
            [
                'name' => 'Admin',
                'password' => env('ADMIN_PASSWORD', 'REDACTED'),
            ]
        );

        PricingSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'base_price_per_hour' => 5.000,
                'currency' => env('PRICING_CURRENCY', 'OMR'),
            ]
        );

        if (DiscountTier::query()->count() === 0) {
            DiscountTier::query()->insert([
                ['min_hours' => 1, 'max_hours' => 1, 'price_per_hour' => 5.000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['min_hours' => 2, 'max_hours' => 2, 'price_per_hour' => 4.500, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['min_hours' => 3, 'max_hours' => null, 'price_per_hour' => 4.000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (Court::query()->count() === 0) {
            foreach (['Court 1', 'Court 2', 'Court 3'] as $name) {
                $court = Court::query()->create(['name' => $name, 'is_active' => true]);

                $workingHours = [];
                for ($day = 0; $day <= 6; $day++) {
                    $workingHours[] = [
                        'court_id' => $court->id,
                        'day_of_week' => $day,
                        'is_closed' => false,
                        'open_time' => '08:00:00',
                        'close_time' => '23:00:00',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                $court->workingHours()->insert($workingHours);
            }
        }
    }
}
