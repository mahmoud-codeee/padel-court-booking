<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Court;
use App\Models\DiscountTier;
use App\Models\PricingSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@padel.local');
        $adminPassword = env('ADMIN_PASSWORD');
        $existingAdmin = Admin::query()->where('email', $adminEmail)->first();

        if (filled($adminPassword)) {
            // Explicit ADMIN_PASSWORD in .env always wins, including on re-seed.
            Admin::query()->updateOrCreate(['email' => $adminEmail], ['name' => 'Admin', 'password' => $adminPassword]);
        } elseif ($existingAdmin) {
            // No password provided and the admin already exists — leave their
            // (possibly already-changed) password alone rather than resetting it.
            $existingAdmin->update(['name' => 'Admin']);
        } else {
            // First-ever seed with no ADMIN_PASSWORD set — generate one rather than
            // shipping a guessable default in a public repo. Shown once, here only.
            $generatedPassword = Str::password(16);
            Admin::query()->create(['email' => $adminEmail, 'name' => 'Admin', 'password' => $generatedPassword]);
            $this->command?->warn("No ADMIN_PASSWORD set — generated one for {$adminEmail}: {$generatedPassword}");
            $this->command?->warn('Save it now (e.g. into your .env as ADMIN_PASSWORD) — it will not be shown again.');
        }

        PricingSetting::query()->updateOrCreate(
            [], // singleton table: match the existing row if any, else create the first one
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
