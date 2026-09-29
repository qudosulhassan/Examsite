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
        Schema::table('order_items', function (Blueprint $table) {
            // Package purchases were previously (incorrectly) storing the
            // package's id in exam_id, which broke order-history/invoice
            // rendering and any code reading exam_id back out. Gives package
            // line items their own unambiguous reference.
            $table->foreignId('package_id')->nullable()->after('exam_id')->constrained('packages')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('package_id');
        });
    }
};
