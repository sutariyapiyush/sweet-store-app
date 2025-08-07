<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = ProductCategory::with(['parent', 'children'])
            ->ordered()
            ->paginate(15);

        return view('product-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentCategories = ProductCategory::active()
            ->root()
            ->ordered()
            ->get();

        return view('product-categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:product_categories,id',
            'image_path' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');

        ProductCategory::create($validated);

        return redirect()->route('product-categories.index')
            ->with('success', 'Product category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductCategory $productCategory)
    {
        $productCategory->load(['parent', 'children', 'products']);

        return view('product-categories.show', compact('productCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductCategory $productCategory)
    {
        $parentCategories = ProductCategory::active()
            ->root()
            ->where('id', '!=', $productCategory->id)
            ->ordered()
            ->get();

        return view('product-categories.edit', compact('productCategory', 'parentCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductCategory $productCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:product_categories,id',
            'image_path' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');

        $productCategory->update($validated);

        return redirect()->route('product-categories.index')
            ->with('success', 'Product category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $productCategory)
    {
        // Check if category has products
        if ($productCategory->products()->count() > 0) {
            return redirect()->route('product-categories.index')
                ->with('error', 'Cannot delete category that has products assigned to it.');
        }

        // Check if category has children
        if ($productCategory->children()->count() > 0) {
            return redirect()->route('product-categories.index')
                ->with('error', 'Cannot delete category that has subcategories.');
        }

        $productCategory->delete();

        return redirect()->route('product-categories.index')
            ->with('success', 'Product category deleted successfully.');
    }
}
