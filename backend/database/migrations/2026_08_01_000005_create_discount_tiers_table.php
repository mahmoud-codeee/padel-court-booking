<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_tiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('min_hours');
            $table->unsignedSmallInteger('max_hours')->nullable(); // null = "N+ hours"
            $table->decimal('price_per_hour', 8, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('min_hours');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_tiers');
    }
};
