<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('court_closures', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_id'); // groups rows created together, e.g. "close 3 courts for Aug 5"
            $table->foreignId('court_id')->nullable()->constrained('courts')->cascadeOnDelete(); // null = all courts
            $table->date('closure_date');
            $table->time('start_time')->nullable(); // null start+end = full-day closure
            $table->time('end_time')->nullable();
            $table->string('reason')->nullable();
            $table->foreignId('created_by')->constrained('admins');
            $table->timestamps();

            $table->index(['closure_date', 'court_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('court_closures');
    }
};
