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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopify_order_id')->constrained('shopify_orders')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->string('shopify_line_item_id')->nullable();
            $table->string('shopify_product_id')->nullable();
            $table->string('shopify_variant_id')->nullable();

            // Product Details
            $table->string('product_title');
            $table->string('variant_title')->nullable();
            $table->string('sku')->nullable();
            $table->string('vendor')->nullable();

            // Quantity and Pricing
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->decimal('total_discount', 10, 2)->default(0);
            $table->decimal('line_total', 10, 2); // price * quantity - discount

            // Product Properties
            $table->json('properties')->nullable(); // Custom properties from Shopify
            $table->json('variant_options')->nullable(); // Size, Color, etc.

            // Fulfillment
            $table->integer('fulfillable_quantity')->default(0);
            $table->integer('fulfilled_quantity')->default(0);
            $table->enum('fulfillment_status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');

            // Tax Information
            $table->boolean('taxable')->default(true);
            $table->json('tax_lines')->nullable();

            // Weight and Shipping
            $table->decimal('grams', 8, 2)->nullable();
            $table->boolean('requires_shipping')->default(true);

            // Gift Card
            $table->boolean('gift_card')->default(false);

            $table->timestamps();

            // Indexes
            $table->index(['shopify_order_id']);
            $table->index(['product_id']);
            $table->index(['shopify_line_item_id']);
            $table->index(['shopify_product_id']);
            $table->index(['sku']);
            $table->index(['fulfillment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
