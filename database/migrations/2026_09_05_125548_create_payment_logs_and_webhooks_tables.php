<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('payment_webhook_logs')) {
            Schema::create('payment_webhook_logs', function (Blueprint $table) {
                $table->id();
                $table->string('gateway', 30); // stripe, paypal
                $table->string('event_type', 100);
                $table->string('event_id', 150)->nullable()->index();
                $table->string('status', 30)->default('pending'); // processed, failed, pending
                $table->longText('payload')->nullable();
                $table->text('error_message')->nullable();
                $table->integer('processing_time_ms')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('payment_activity_logs')) {
            Schema::create('payment_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->string('gateway', 30)->nullable(); // stripe, paypal, system
                $table->string('event', 50); // payment_created, payment_completed, payment_failed, refund_created, webhook_received, gateway_error
                $table->string('status', 30)->default('info'); // success, error, warning, info
                $table->text('message');
                $table->longText('payload')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_activity_logs');
        Schema::dropIfExists('payment_webhook_logs');
    }
};
