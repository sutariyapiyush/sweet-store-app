<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\RawMaterial;
use App\Models\Product;
use App\Models\ProductionLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@sweetstore.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create staff user
        $staff = User::create([
            'name' => 'Staff User',
            'email' => 'staff@sweetstore.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);

        // Create raw materials
        $milk = RawMaterial::create([
            'name' => 'Milk',
            'unit' => 'liter',
            'quantity_in_stock' => 50.00,
        ]);

        $sugar = RawMaterial::create([
            'name' => 'Sugar',
            'unit' => 'kg',
            'quantity_in_stock' => 25.00,
        ]);

        $flour = RawMaterial::create([
            'name' => 'Flour',
            'unit' => 'kg',
            'quantity_in_stock' => 8.00, // Low stock
        ]);

        $ghee = RawMaterial::create([
            'name' => 'Ghee',
            'unit' => 'kg',
            'quantity_in_stock' => 15.00,
        ]);

        $cardamom = RawMaterial::create([
            'name' => 'Cardamom',
            'unit' => 'kg',
            'quantity_in_stock' => 2.00,
        ]);

        // Create products
        $rasgulla = Product::create([
            'name' => 'Rasgulla',
            'description' => 'Traditional Bengali sweet made from milk',
            'unit' => 'pieces',
            'quantity_in_stock' => 50,
        ]);

        $gulabjamun = Product::create([
            'name' => 'Gulab Jamun',
            'description' => 'Deep-fried milk balls in sugar syrup',
            'unit' => 'pieces',
            'quantity_in_stock' => 5, // Low stock
        ]);

        $barfi = Product::create([
            'name' => 'Milk Barfi',
            'description' => 'Sweet milk fudge with cardamom',
            'unit' => 'pieces',
            'quantity_in_stock' => 30,
        ]);

        // Create BOM relationships (Bill of Materials)
        // Rasgulla = 1L Milk + 200g Sugar
        $rasgulla->rawMaterials()->attach($milk->id, ['quantity_required' => 1.0]);
        $rasgulla->rawMaterials()->attach($sugar->id, ['quantity_required' => 0.2]);

        // Gulab Jamun = 0.5L Milk + 150g Sugar + 100g Flour + 50g Ghee
        $gulabjamun->rawMaterials()->attach($milk->id, ['quantity_required' => 0.5]);
        $gulabjamun->rawMaterials()->attach($sugar->id, ['quantity_required' => 0.15]);
        $gulabjamun->rawMaterials()->attach($flour->id, ['quantity_required' => 0.1]);
        $gulabjamun->rawMaterials()->attach($ghee->id, ['quantity_required' => 0.05]);

        // Milk Barfi = 1.5L Milk + 300g Sugar + 10g Cardamom
        $barfi->rawMaterials()->attach($milk->id, ['quantity_required' => 1.5]);
        $barfi->rawMaterials()->attach($sugar->id, ['quantity_required' => 0.3]);
        $barfi->rawMaterials()->attach($cardamom->id, ['quantity_required' => 0.01]);

        // Create some production logs
        ProductionLog::create([
            'product_id' => $rasgulla->id,
            'quantity_produced' => 20,
            'created_at' => now()->subDays(2),
        ]);

        ProductionLog::create([
            'product_id' => $gulabjamun->id,
            'quantity_produced' => 15,
            'created_at' => now()->subDay(),
        ]);

        ProductionLog::create([
            'product_id' => $barfi->id,
            'quantity_produced' => 10,
            'created_at' => now(),
        ]);
    }
}
