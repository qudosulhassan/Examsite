<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            Schema::disableForeignKeyConstraints();

            // 1. Recreate order_items table without strict enum check
            Schema::create('order_items_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->onDelete('cascade');
                $table->foreignId('exam_id')->nullable()->constrained()->onDelete('set null');
                $table->string('plan_name')->nullable();
                $table->string('item_type'); // Allows pdf, engine_single, subscription, package, combo
                $table->decimal('price', 8, 2);
                $table->timestamps();
            });

            DB::statement('INSERT INTO order_items_temp (id, order_id, exam_id, plan_name, item_type, price, created_at, updated_at) SELECT id, order_id, exam_id, plan_name, item_type, price, created_at, updated_at FROM order_items');

            Schema::drop('order_items');
            Schema::rename('order_items_temp', 'order_items');

            // 2. Recreate orders table without strict payment_method enum check
            Schema::create('orders_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('order_number')->unique();
                $table->decimal('subtotal', 8, 2);
                $table->decimal('discount_amount', 8, 2)->default(0.00);
                $table->decimal('total_amount', 8, 2);
                $table->foreignId('coupon_id')->nullable()->constrained()->onDelete('set null');
                $table->string('payment_method')->default('stripe'); // Allows stripe, paypal, free, mock
                $table->string('payment_status')->default('pending');
                $table->string('stripe_payment_intent_id')->nullable();
                $table->string('paypal_order_id')->nullable();
                $table->string('billing_name');
                $table->string('billing_email');
                $table->timestamps();
            });

            DB::statement('INSERT INTO orders_temp (id, user_id, order_number, subtotal, discount_amount, total_amount, coupon_id, payment_method, payment_status, stripe_payment_intent_id, paypal_order_id, billing_name, billing_email, created_at, updated_at) SELECT id, user_id, order_number, subtotal, discount_amount, total_amount, coupon_id, payment_method, payment_status, stripe_payment_intent_id, paypal_order_id, billing_name, billing_email, created_at, updated_at FROM orders');

            Schema::drop('orders');
            Schema::rename('orders_temp', 'orders');

            Schema::enableForeignKeyConstraints();
        } else {
            Schema::table('order_items', function (Blueprint $table) {
                $table->string('item_type')->change();
            });

            Schema::table('orders', function (Blueprint $table) {
                $table->string('payment_method')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op down migration
    }
};
