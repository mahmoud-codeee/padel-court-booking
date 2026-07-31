<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('court_working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0=Sun .. 6=Sat (Carbon::dayOfWeek)
            $table->boolean('is_closed')->default(false);
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->timestamps();

            $table->unique(['court_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('court_working_hours');
    }
};
