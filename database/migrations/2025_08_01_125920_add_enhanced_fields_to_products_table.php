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
        Schema::table('products', function (Blueprint $table) {
            // Pricing & Cost Information
            $table->decimal('cost_price', 10, 2)->nullable()->after('quantity_in_stock');
            $table->decimal('selling_price', 10, 2)->nullable()->after('cost_price');
            $table->decimal('wholesale_price', 10, 2)->nullable()->after('selling_price');
            $table->decimal('profit_margin', 5, 2)->nullable()->after('wholesale_price');

            // Product Classification
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->onDelete('set null')->after('profit_margin');
            $table->string('sku')->unique()->nullable()->after('category_id');
            $table->string('barcode')->unique()->nullable()->after('sku');

            // Physical Properties
            $table->decimal('weight', 8, 3)->nullable()->after('barcode');
            $table->json('dimensions')->nullable()->after('weight'); // {length, width, height}
            $table->integer('shelf_life_days')->nullable()->after('dimensions');
            $table->enum('storage_temperature', ['room_temp', 'refrigerated', 'frozen'])->default('room_temp')->after('shelf_life_days');

            // Inventory Management
            $table->decimal('minimum_stock_level', 10, 2)->default(10)->after('storage_temperature');
            $table->decimal('maximum_stock_level', 10, 2)->nullable()->after('minimum_stock_level');
            $table->decimal('reorder_quantity', 10, 2)->nullable()->after('maximum_stock_level');

            // Product Status & Availability
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active')->after('reorder_quantity');
            $table->boolean('is_seasonal')->default(false)->after('status');
            $table->json('seasonal_months')->nullable()->after('is_seasonal'); // [1,2,3,4,5,6,7,8,9,10,11,12]
            $table->integer('production_time_minutes')->nullable()->after('seasonal_months');

            // Marketing & Display
            $table->string('image_path')->nullable()->after('production_time_minutes');
            $table->text('ingredients_list')->nullable()->after('image_path');
            $table->json('nutritional_info')->nullable()->after('ingredients_list'); // {calories, fat, sugar, etc}
            $table->json('allergens')->nullable()->after('nutritional_info'); // [nuts, dairy, gluten, etc]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn([
                'cost_price',
                'selling_price',
                'wholesale_price',
                'profit_margin',
                'category_id',
                'sku',
                'barcode',
                'weight',
                'dimensions',
                'shelf_life_days',
                'storage_temperature',
                'minimum_stock_level',
                'maximum_stock_level',
                'reorder_quantity',
                'status',
                'is_seasonal',
                'seasonal_months',
                'production_time_minutes',
                'image_path',
                'ingredients_list',
                'nutritional_info',
                'allergens',
            ]);
        });
    }
};
