<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Singleton table: exactly one row (id=1), enforced by PricingService.
        Schema::create('pricing_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('base_price_per_hour', 8, 2);
            $table->char('currency', 3)->default('OMR');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_settings');
    }
};
