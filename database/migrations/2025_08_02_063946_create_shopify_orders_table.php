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
        Schema::create('shopify_orders', function (Blueprint $table) {
            $table->id();
            $table->string('shopify_order_id')->unique();
            $table->string('order_number');
            $table->string('name'); // Shopify order name like #1001

            // Customer Information
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->json('customer_data')->nullable(); // Full customer object from Shopify

            // Order Details
            $table->enum('financial_status', ['pending', 'authorized', 'partially_paid', 'paid', 'partially_refunded', 'refunded', 'voided'])->default('pending');
            $table->enum('fulfillment_status', ['fulfilled', 'null', 'partial', 'restocked'])->nullable();
            $table->decimal('total_price', 10, 2);
            $table->decimal('subtotal_price', 10, 2);
            $table->decimal('total_tax', 10, 2)->default(0);
            $table->decimal('total_discounts', 10, 2)->default(0);
            $table->decimal('shipping_price', 10, 2)->default(0);
            $table->string('currency', 3)->default('INR');

            // Addresses
            $table->json('billing_address')->nullable();
            $table->json('shipping_address')->nullable();

            // Internal Status Management
            $table->enum('internal_status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'])->default('pending');
            $table->boolean('is_synced')->default(false);
            $table->timestamp('synced_at')->nullable();

            // Invoice and QR Code
            $table->string('invoice_number')->nullable()->unique();
            $table->string('qr_code_path')->nullable();
            $table->timestamp('invoice_generated_at')->nullable();

            // Shopify Timestamps
            $table->timestamp('shopify_created_at')->nullable();
            $table->timestamp('shopify_updated_at')->nullable();
            $table->timestamp('processed_at')->nullable();

            // Additional Data
            $table->json('tags')->nullable();
            $table->text('note')->nullable();
            $table->json('note_attributes')->nullable();
            $table->json('discount_codes')->nullable();
            $table->json('tax_lines')->nullable();
            $table->json('shipping_lines')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['shopify_order_id']);
            $table->index(['order_number']);
            $table->index(['customer_email']);
            $table->index(['internal_status']);
            $table->index(['financial_status']);
            $table->index(['fulfillment_status']);
            $table->index(['is_synced']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopify_orders');
    }
};
