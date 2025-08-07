<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with(['category', 'variants'])
            ->active()
            ->paginate(15);

        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = ProductCategory::active()->ordered()->get();
        $rawMaterials = RawMaterial::all();

        return view('products.create', compact('categories', 'rawMaterials'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'quantity_in_stock' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:product_categories,id',
            'sku' => 'nullable|string|max:255|unique:products,sku',
            'barcode' => 'nullable|string|max:255|unique:products,barcode',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|array',
            'shelf_life_days' => 'nullable|integer|min:1',
            'storage_temperature' => 'nullable|in:room_temp,refrigerated,frozen',
            'minimum_stock_level' => 'nullable|numeric|min:0',
            'maximum_stock_level' => 'nullable|numeric|min:0',
            'reorder_quantity' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,discontinued',
            'is_seasonal' => 'boolean',
            'seasonal_months' => 'nullable|array',
            'production_time_minutes' => 'nullable|integer|min:1',
            'image_path' => 'nullable|string|max:255',
            'ingredients_list' => 'nullable|string',
            'nutritional_info' => 'nullable|array',
            'allergens' => 'nullable|array',
            'raw_materials' => 'nullable|array',
            'raw_materials.*.id' => 'exists:raw_materials,id',
            'raw_materials.*.quantity_required' => 'required|numeric|min:0',
        ]);

        // Generate SKU if not provided
        if (empty($validated['sku'])) {
            $validated['sku'] = $this->generateSku($validated['name']);
        }

        // Calculate profit margin if prices are provided
        if ($validated['cost_price'] && $validated['selling_price']) {
            $validated['profit_margin'] = (($validated['selling_price'] - $validated['cost_price']) / $validated['cost_price']) * 100;
        }

        $validated['is_seasonal'] = $request->has('is_seasonal');

        $product = Product::create($validated);

        // Attach raw materials if provided
        if (!empty($validated['raw_materials'])) {
            $rawMaterialsData = [];
            foreach ($validated['raw_materials'] as $rawMaterial) {
                $rawMaterialsData[$rawMaterial['id']] = [
                    'quantity_required' => $rawMaterial['quantity_required']
                ];
            }
            $product->rawMaterials()->attach($rawMaterialsData);
        }

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load(['category', 'variants', 'rawMaterials', 'productionLogs.user', 'productionSchedules']);

        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = ProductCategory::active()->ordered()->get();
        $rawMaterials = RawMaterial::all();
        $product->load('rawMaterials');

        return view('products.edit', compact('product', 'categories', 'rawMaterials'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'quantity_in_stock' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:product_categories,id',
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $product->id,
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|array',
            'shelf_life_days' => 'nullable|integer|min:1',
            'storage_temperature' => 'nullable|in:room_temp,refrigerated,frozen',
            'minimum_stock_level' => 'nullable|numeric|min:0',
            'maximum_stock_level' => 'nullable|numeric|min:0',
            'reorder_quantity' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,discontinued',
            'is_seasonal' => 'boolean',
            'seasonal_months' => 'nullable|array',
            'production_time_minutes' => 'nullable|integer|min:1',
            'image_path' => 'nullable|string|max:255',
            'ingredients_list' => 'nullable|string',
            'nutritional_info' => 'nullable|array',
            'allergens' => 'nullable|array',
            'raw_materials' => 'nullable|array',
            'raw_materials.*.id' => 'exists:raw_materials,id',
            'raw_materials.*.quantity_required' => 'required|numeric|min:0',
        ]);

        // Calculate profit margin if prices are provided
        if ($validated['cost_price'] && $validated['selling_price']) {
            $validated['profit_margin'] = (($validated['selling_price'] - $validated['cost_price']) / $validated['cost_price']) * 100;
        }

        $validated['is_seasonal'] = $request->has('is_seasonal');

        $product->update($validated);

        // Sync raw materials if provided
        if (isset($validated['raw_materials'])) {
            $rawMaterialsData = [];
            foreach ($validated['raw_materials'] as $rawMaterial) {
                $rawMaterialsData[$rawMaterial['id']] = [
                    'quantity_required' => $rawMaterial['quantity_required']
                ];
            }
            $product->rawMaterials()->sync($rawMaterialsData);
        }

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Check if product has production logs
        if ($product->productionLogs()->count() > 0) {
            return redirect()->route('products.index')
                ->with('error', 'Cannot delete product that has production history.');
        }

        // Check if product has scheduled productions
        if ($product->productionSchedules()->count() > 0) {
            return redirect()->route('products.index')
                ->with('error', 'Cannot delete product that has scheduled productions.');
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Generate a unique SKU for the product
     */
    private function generateSku(string $name): string
    {
        $base = strtoupper(Str::slug($name, ''));
        $base = substr($base, 0, 8);

        $counter = 1;
        $sku = $base . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);

        while (Product::where('sku', $sku)->exists()) {
            $counter++;
            $sku = $base . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);
        }

        return $sku;
    }
}
