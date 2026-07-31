<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('court_id')->constrained('courts'); // assigned at creation, never null
            $table->date('slot_date');
            $table->unsignedTinyInteger('slot_hour'); // 0-23
            $table->decimal('price_per_hour_charged', 8, 2);
            $table->timestamps();

            // The database-level guarantee that no court-hour is ever double-sold.
            $table->unique(['court_id', 'slot_date', 'slot_hour']);
            $table->index(['slot_date', 'slot_hour']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_slots');
    }
};
