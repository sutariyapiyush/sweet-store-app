<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductionSchedule;
use App\Models\ProductionLog;
use App\Models\QualityCheck;
use App\Models\User;
use App\Models\RawMaterial;
use Carbon\Carbon;

class EnhancedProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Product Categories
        $categories = [
            [
                'name' => 'Cakes',
                'slug' => 'cakes',
                'description' => 'Various types of cakes and cake products',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Cookies',
                'slug' => 'cookies',
                'description' => 'Assorted cookies and biscuits',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Pastries',
                'slug' => 'pastries',
                'description' => 'Fresh pastries and baked goods',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Breads',
                'slug' => 'breads',
                'description' => 'Daily fresh breads',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($categories as $categoryData) {
            ProductCategory::create($categoryData);
        }

        // Get created categories
        $cakeCategory = ProductCategory::where('slug', 'cakes')->first();
        $cookieCategory = ProductCategory::where('slug', 'cookies')->first();
        $pastryCategory = ProductCategory::where('slug', 'pastries')->first();
        $breadCategory = ProductCategory::where('slug', 'breads')->first();

        // Create Enhanced Products
        $products = [
            [
                'name' => 'Chocolate Cake',
                'description' => 'Rich chocolate cake with chocolate frosting',
                'unit' => 'pieces',
                'quantity_in_stock' => 15,
                'cost_price' => 12.50,
                'selling_price' => 25.00,
                'wholesale_price' => 20.00,
                'category_id' => $cakeCategory->id,
                'sku' => 'CAKE-CHOC-001',
                'barcode' => '1234567890123',
                'weight' => 1.2,
                'dimensions' => ['length' => 20, 'width' => 20, 'height' => 8],
                'shelf_life_days' => 5,
                'storage_temperature' => 'room_temp',
                'minimum_stock_level' => 5,
                'maximum_stock_level' => 50,
                'reorder_quantity' => 20,
                'status' => 'active',
                'is_seasonal' => false,
                'production_time_minutes' => 120,
                'ingredients_list' => 'Flour, Sugar, Cocoa, Eggs, Butter, Milk',
                'nutritional_info' => ['calories' => 350, 'fat' => 15, 'sugar' => 25],
                'allergens' => ['gluten', 'dairy', 'eggs'],
            ],
            [
                'name' => 'Vanilla Cupcakes',
                'description' => 'Light and fluffy vanilla cupcakes',
                'unit' => 'pieces',
                'quantity_in_stock' => 24,
                'cost_price' => 2.00,
                'selling_price' => 4.50,
                'wholesale_price' => 3.50,
                'category_id' => $cakeCategory->id,
                'sku' => 'CAKE-VAN-CUP',
                'weight' => 0.08,
                'shelf_life_days' => 3,
                'storage_temperature' => 'room_temp',
                'minimum_stock_level' => 12,
                'maximum_stock_level' => 100,
                'reorder_quantity' => 48,
                'status' => 'active',
                'production_time_minutes' => 45,
                'ingredients_list' => 'Flour, Sugar, Vanilla, Eggs, Butter',
                'allergens' => ['gluten', 'dairy', 'eggs'],
            ],
            [
                'name' => 'Chocolate Chip Cookies',
                'description' => 'Classic chocolate chip cookies',
                'unit' => 'pieces',
                'quantity_in_stock' => 48,
                'cost_price' => 0.75,
                'selling_price' => 2.00,
                'wholesale_price' => 1.50,
                'category_id' => $cookieCategory->id,
                'sku' => 'COOK-CHOC-CHIP',
                'weight' => 0.03,
                'shelf_life_days' => 7,
                'storage_temperature' => 'room_temp',
                'minimum_stock_level' => 24,
                'maximum_stock_level' => 200,
                'reorder_quantity' => 100,
                'status' => 'active',
                'production_time_minutes' => 30,
                'ingredients_list' => 'Flour, Sugar, Chocolate Chips, Butter, Eggs',
                'allergens' => ['gluten', 'dairy', 'eggs'],
            ],
            [
                'name' => 'Croissants',
                'description' => 'Buttery, flaky croissants',
                'unit' => 'pieces',
                'quantity_in_stock' => 18,
                'cost_price' => 1.50,
                'selling_price' => 3.50,
                'wholesale_price' => 2.75,
                'category_id' => $pastryCategory->id,
                'sku' => 'PAST-CROIS-001',
                'weight' => 0.06,
                'shelf_life_days' => 2,
                'storage_temperature' => 'room_temp',
                'minimum_stock_level' => 12,
                'maximum_stock_level' => 60,
                'reorder_quantity' => 36,
                'status' => 'active',
                'production_time_minutes' => 180, // Including rising time
                'ingredients_list' => 'Flour, Butter, Yeast, Salt, Sugar',
                'allergens' => ['gluten', 'dairy'],
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        // Create Product Variants
        $chocolateCake = Product::where('sku', 'CAKE-CHOC-001')->first();
        $variants = [
            [
                'product_id' => $chocolateCake->id,
                'variant_name' => 'Small',
                'variant_type' => 'size',
                'price_modifier' => -5.00,
                'sku_suffix' => '-SM',
                'weight_modifier' => -0.4,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'product_id' => $chocolateCake->id,
                'variant_name' => 'Large',
                'variant_type' => 'size',
                'price_modifier' => 10.00,
                'sku_suffix' => '-LG',
                'weight_modifier' => 0.8,
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($variants as $variantData) {
            ProductVariant::create($variantData);
        }

        // Get first user for production logs
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Production Manager',
                'email' => 'production@sweetstore.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]);
        }

        // Create Production Schedules
        $schedules = [
            [
                'product_id' => $chocolateCake->id,
                'scheduled_date' => Carbon::today()->addDay(),
                'scheduled_time' => '09:00:00',
                'planned_quantity' => 10,
                'priority' => 'high',
                'status' => 'scheduled',
                'assigned_user_id' => $user->id,
                'notes' => 'Weekend special order',
                'estimated_duration_minutes' => 240,
                'due_date' => Carbon::today()->addDays(2),
            ],
            [
                'product_id' => Product::where('sku', 'COOK-CHOC-CHIP')->first()->id,
                'scheduled_date' => Carbon::today(),
                'scheduled_time' => '14:00:00',
                'planned_quantity' => 100,
                'priority' => 'medium',
                'status' => 'in_progress',
                'assigned_user_id' => $user->id,
                'estimated_duration_minutes' => 120,
            ],
        ];

        foreach ($schedules as $scheduleData) {
            ProductionSchedule::create($scheduleData);
        }

        // Create Production Logs
        $productionLogs = [
            [
                'product_id' => $chocolateCake->id,
                'quantity_produced' => 8,
                'batch_number' => 'BATCH-' . date('Ymd') . '-001',
                'production_date' => Carbon::yesterday(),
                'expiry_date' => Carbon::yesterday()->addDays(5),
                'user_id' => $user->id,
                'shift' => 'morning',
                'production_time_minutes' => 135,
                'yield_percentage' => 95.5,
                'quality_grade' => 'A',
                'waste_quantity' => 0.5,
                'labor_cost' => 25.00,
                'overhead_cost' => 15.00,
                'total_production_cost' => 140.00,
                'notes' => 'Perfect batch, excellent quality',
                'status' => 'completed',
            ],
            [
                'product_id' => Product::where('sku', 'COOK-CHOC-CHIP')->first()->id,
                'quantity_produced' => 95,
                'batch_number' => 'BATCH-' . date('Ymd') . '-002',
                'production_date' => Carbon::today(),
                'user_id' => $user->id,
                'shift' => 'afternoon',
                'production_time_minutes' => 35,
                'yield_percentage' => 98.0,
                'quality_grade' => 'A',
                'waste_quantity' => 2,
                'labor_cost' => 12.00,
                'overhead_cost' => 8.00,
                'total_production_cost' => 91.25,
                'status' => 'completed',
            ],
        ];

        foreach ($productionLogs as $logData) {
            ProductionLog::create($logData);
        }

        // Create Quality Checks
        $productionLog = ProductionLog::first();
        $qualityChecks = [
            [
                'production_log_id' => $productionLog->id,
                'check_type' => 'visual',
                'result' => 'pass',
                'notes' => 'Excellent appearance, even browning',
                'checked_by' => $user->id,
                'checked_at' => Carbon::now(),
            ],
            [
                'production_log_id' => $productionLog->id,
                'check_type' => 'taste',
                'result' => 'pass',
                'notes' => 'Rich chocolate flavor, perfect sweetness',
                'checked_by' => $user->id,
                'checked_at' => Carbon::now(),
            ],
            [
                'production_log_id' => $productionLog->id,
                'check_type' => 'weight',
                'result' => 'pass',
                'measured_value' => 1.18,
                'expected_value' => 1.20,
                'tolerance' => 0.05,
                'checked_by' => $user->id,
                'checked_at' => Carbon::now(),
            ],
        ];

        foreach ($qualityChecks as $checkData) {
            QualityCheck::create($checkData);
        }

        $this->command->info('Enhanced production data seeded successfully!');
    }
}
