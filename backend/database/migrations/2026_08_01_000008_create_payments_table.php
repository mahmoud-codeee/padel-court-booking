<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('provider', 20)->default('thawani');
            $table->string('client_reference_id', 64)->unique();
            $table->string('thawani_session_id', 100)->unique()->nullable();
            $table->text('thawani_checkout_url')->nullable();
            $table->string('thawani_status', 30)->nullable();
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3);
            $table->json('raw_session_response')->nullable();
            $table->json('raw_webhook_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
