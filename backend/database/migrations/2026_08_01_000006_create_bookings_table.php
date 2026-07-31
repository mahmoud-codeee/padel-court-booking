<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('reference')->unique(); // used in public URLs, non-enumerable
            $table->string('customer_phone', 20);
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->unsignedSmallInteger('total_hours');
            $table->decimal('price_per_hour_applied', 8, 2); // snapshot of the tier rate used
            $table->decimal('total_amount', 10, 2);
            $table->char('currency', 3);
            $table->enum('payment_method', ['cash', 'online']);
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'expired'])->default('pending');
            $table->enum('payment_status', [
                'unpaid', 'awaiting_payment', 'paid', 'failed', 'refund_pending', 'refunded',
            ])->default('unpaid');
            $table->timestamp('hold_expires_at')->nullable(); // only set while payment_method=online and pending
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('admin_notes')->nullable();
            $table->timestamps();

            $table->index('customer_phone');
            $table->index('status');
            $table->index('payment_method');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
