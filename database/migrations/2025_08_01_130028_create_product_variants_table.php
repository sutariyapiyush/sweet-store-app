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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('variant_name'); // e.g., "Small", "Large", "Chocolate", "Vanilla"
            $table->string('variant_type'); // e.g., "size", "flavor", "color"
            $table->decimal('price_modifier', 10, 2)->default(0); // +/- amount from base price
            $table->string('sku_suffix')->nullable(); // e.g., "-SM", "-LG", "-CHOC"
            $table->decimal('weight_modifier', 8, 3)->default(0); // +/- weight from base product
            $table->json('additional_attributes')->nullable(); // Extra variant-specific data
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'variant_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
