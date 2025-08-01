<?php

namespace App\Http\Controllers;

use App\Models\RawMaterial;
use App\Models\Product;
use App\Models\ProductionLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Get filter parameters
        $search = $request->get('search');
        $type = $request->get('type', 'all'); // all, raw_materials, products

        // Get raw materials with low stock alert
        $rawMaterialsQuery = RawMaterial::query();
        if ($search && ($type === 'all' || $type === 'raw_materials')) {
            $rawMaterialsQuery->where('name', 'like', "%{$search}%");
        }
        $rawMaterials = $rawMaterialsQuery->orderBy('name')->get();
        $lowStockRawMaterials = $rawMaterials->filter(fn($item) => $item->isLowStock());

        // Get products with low stock alert
        $productsQuery = Product::query();
        if ($search && ($type === 'all' || $type === 'products')) {
            $productsQuery->where('name', 'like', "%{$search}%");
        }
        $products = $productsQuery->orderBy('name')->get();
        $lowStockProducts = $products->filter(fn($item) => $item->isLowStock());

        // Get recent production logs
        $recentProduction = ProductionLog::with('product')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Filter data based on type
        if ($type === 'raw_materials') {
            $products = collect();
            $lowStockProducts = collect();
        } elseif ($type === 'products') {
            $rawMaterials = collect();
            $lowStockRawMaterials = collect();
        }

        return view('dashboard', compact(
            'rawMaterials',
            'products',
            'lowStockRawMaterials',
            'lowStockProducts',
            'recentProduction',
            'search',
            'type'
        ));
    }
}
