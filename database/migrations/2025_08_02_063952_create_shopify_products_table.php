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
        Schema::create('shopify_products', function (Blueprint $table) {
            $table->id();
            $table->string('shopify_product_id')->unique();
            $table->foreignId('local_product_id')->nullable()->constrained('products')->onDelete('set null');

            // Basic Product Information
            $table->string('title');
            $table->text('body_html')->nullable();
            $table->string('vendor')->nullable();
            $table->string('product_type')->nullable();
            $table->string('handle')->unique();

            // Status and Visibility
            $table->enum('status', ['active', 'archived', 'draft'])->default('active');
            $table->string('published_scope')->default('web');
            $table->timestamp('published_at')->nullable();

            // SEO and Meta
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();

            // Product Options and Variants
            $table->json('options')->nullable(); // Color, Size, etc.
            $table->json('images')->nullable();
            $table->string('featured_image')->nullable();

            // Sync Information
            $table->boolean('is_synced_to_local')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->json('sync_errors')->nullable();

            // Shopify Timestamps
            $table->timestamp('shopify_created_at')->nullable();
            $table->timestamp('shopify_updated_at')->nullable();

            // Additional Shopify Data
            $table->json('tags')->nullable();
            $table->string('template_suffix')->nullable();
            $table->json('metafields')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['shopify_product_id']);
            $table->index(['local_product_id']);
            $table->index(['handle']);
            $table->index(['status']);
            $table->index(['is_synced_to_local']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopify_products');
    }
};
