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
        Schema::table('shopify_orders', function (Blueprint $table) {
            $table->string('tracking_number')->nullable()->after('invoice_generated_at');
            $table->string('shipping_partner')->nullable()->after('tracking_number');
            $table->timestamp('shipped_at')->nullable()->after('shipping_partner');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shopify_orders', function (Blueprint $table) {
            $table->dropColumn(['tracking_number', 'shipping_partner', 'shipped_at']);
        });
    }
};
