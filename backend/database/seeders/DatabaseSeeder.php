<?php

namespace Database\Seeders;

use App\Models\Admin;
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
    }
}
