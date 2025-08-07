<?php

namespace App\Http\Controllers;

use App\Models\ProductionLog;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductionLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ProductionLog::with(['product', 'user', 'qualityChecks'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('batch_number')) {
            $query->where('batch_number', 'like', '%' . $request->batch_number . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('efficiency_min')) {
            $query->where('yield_percentage', '>=', $request->efficiency_min);
        }

        $productionLogs = $query->paginate(15);

        // Get filter options
        $products = Product::orderBy('name')->get();

        // Calculate statistics
        $stats = [
            'total_logs' => ProductionLog::count(),
            'completed' => ProductionLog::where('status', 'completed')->count(),
            'in_progress' => ProductionLog::where('status', 'in_progress')->count(),
            'avg_efficiency' => ProductionLog::whereNotNull('yield_percentage')->avg('yield_percentage'),
            'total_quantity' => ProductionLog::sum('quantity_produced'),
        ];

        return view('production-logs.index', compact('productionLogs', 'products', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::orderBy('name')->get();
        $rawMaterials = RawMaterial::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('production-logs.create', compact('products', 'rawMaterials', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_number' => 'nullable|string|max:255',
            'quantity_produced' => 'required|numeric|min:0',
            'production_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
            'raw_materials_used' => 'nullable|json',
            'equipment_used' => 'nullable|string',
            'staff_assigned' => 'nullable|string',
            'temperature' => 'nullable|numeric',
            'humidity' => 'nullable|numeric',
            'pressure' => 'nullable|numeric',
            'ph_level' => 'nullable|numeric',
            'yield_percentage' => 'nullable|numeric|min:0|max:100',
            'waste_quantity' => 'nullable|numeric|min:0',
            'labor_cost' => 'nullable|numeric|min:0',
            'overhead_cost' => 'nullable|numeric|min:0',
            'total_production_cost' => 'nullable|numeric|min:0',
            'shift' => 'nullable|in:morning,afternoon,night',
            'quality_grade' => 'nullable|in:A,B,C,D',
            'production_time_minutes' => 'nullable|numeric|min:0',
            'issues_encountered' => 'nullable|string',
            'temperature_log' => 'nullable|json',
        ]);

        // Generate batch number if not provided
        if (empty($validated['batch_number'])) {
            $validated['batch_number'] = $this->generateBatchNumber($validated['product_id']);
        }

        // Set user_id to current user
        $validated['user_id'] = Auth::id();

        // Calculate production time if start and end times are provided
        if ($validated['start_time'] && $validated['end_time']) {
            $start = \Carbon\Carbon::createFromFormat('H:i', $validated['start_time']);
            $end = \Carbon\Carbon::createFromFormat('H:i', $validated['end_time']);
            $validated['production_time_minutes'] = $end->diffInMinutes($start);
        }

        $productionLog = ProductionLog::create($validated);

        return redirect()->route('production-logs.show', $productionLog)
            ->with('success', 'Production log created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductionLog $productionLog)
    {
        $productionLog->load(['product', 'user', 'qualityChecks']);

        return view('production-logs.show', compact('productionLog'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductionLog $productionLog)
    {
        $products = Product::orderBy('name')->get();
        $rawMaterials = RawMaterial::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('production-logs.edit', compact('productionLog', 'products', 'rawMaterials', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductionLog $productionLog)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_number' => 'nullable|string|max:255',
            'quantity_produced' => 'required|numeric|min:0',
            'production_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
            'raw_materials_used' => 'nullable|json',
            'equipment_used' => 'nullable|string',
            'staff_assigned' => 'nullable|string',
            'temperature' => 'nullable|numeric',
            'humidity' => 'nullable|numeric',
            'pressure' => 'nullable|numeric',
            'ph_level' => 'nullable|numeric',
            'yield_percentage' => 'nullable|numeric|min:0|max:100',
            'waste_quantity' => 'nullable|numeric|min:0',
            'labor_cost' => 'nullable|numeric|min:0',
            'overhead_cost' => 'nullable|numeric|min:0',
            'total_production_cost' => 'nullable|numeric|min:0',
            'shift' => 'nullable|in:morning,afternoon,night',
            'quality_grade' => 'nullable|in:A,B,C,D',
            'production_time_minutes' => 'nullable|numeric|min:0',
            'issues_encountered' => 'nullable|string',
            'temperature_log' => 'nullable|json',
        ]);

        // Calculate production time if start and end times are provided
        if ($validated['start_time'] && $validated['end_time']) {
            $start = \Carbon\Carbon::createFromFormat('H:i', $validated['start_time']);
            $end = \Carbon\Carbon::createFromFormat('H:i', $validated['end_time']);
            $validated['production_time_minutes'] = $end->diffInMinutes($start);
        }

        $productionLog->update($validated);

        return redirect()->route('production-logs.show', $productionLog)
            ->with('success', 'Production log updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductionLog $productionLog)
    {
        $productionLog->delete();

        return redirect()->route('production-logs.index')
            ->with('success', 'Production log deleted successfully.');
    }

    /**
     * Display production analytics dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_production' => ProductionLog::sum('quantity_produced'),
            'avg_efficiency' => ProductionLog::whereNotNull('yield_percentage')->avg('yield_percentage'),
            'total_batches' => ProductionLog::count(),
            'completed_batches' => ProductionLog::where('status', 'completed')->count(),
            'avg_quality_grade' => ProductionLog::whereNotNull('quality_grade')->avg('quality_grade'),
            'total_waste' => ProductionLog::sum('waste_quantity'),
            'total_cost' => ProductionLog::sum('total_production_cost'),
        ];

        // Recent production logs
        $recentLogs = ProductionLog::with(['product', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Production by product (top 10)
        $productionByProduct = ProductionLog::select('product_id', DB::raw('SUM(quantity_produced) as total_quantity'))
            ->with('product')
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        // Efficiency trends (last 30 days)
        $efficiencyTrends = ProductionLog::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('AVG(yield_percentage) as avg_efficiency')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('yield_percentage')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('production-logs.dashboard', compact(
            'stats', 'recentLogs', 'productionByProduct', 'efficiencyTrends'
        ));
    }

    /**
     * Update production status
     */
    public function updateStatus(Request $request, ProductionLog $productionLog)
    {
        $request->validate([
            'status' => 'required|in:planned,in_progress,completed,cancelled'
        ]);

        $productionLog->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Production status updated successfully.');
    }

    /**
     * Generate a unique batch number
     */
    private function generateBatchNumber($productId)
    {
        $product = Product::find($productId);
        $prefix = $product ? strtoupper(substr($product->sku ?? $product->name, 0, 3)) : 'PRD';
        $date = now()->format('Ymd');
        $sequence = ProductionLog::where('product_id', $productId)
            ->whereDate('created_at', today())
            ->count() + 1;

        return $prefix . '-' . $date . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get production logs for a specific product
     */
    public function forProduct(Product $product)
    {
        $productionLogs = ProductionLog::where('product_id', $product->id)
            ->with(['user', 'qualityChecks'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('production-logs.for-product', compact('productionLogs', 'product'));
    }

    /**
     * Export production logs
     */
    public function export(Request $request)
    {
        $query = ProductionLog::with(['product', 'user']);

        // Apply same filters as index
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $productionLogs = $query->get();

        // Return CSV download
        $filename = 'production_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($productionLogs) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Batch Number', 'Product', 'Quantity Produced', 'Production Date',
                'Status', 'Yield %', 'Quality Grade', 'Waste Quantity',
                'Production Cost', 'Labor Cost', 'Created By'
            ]);

            // CSV data
            foreach ($productionLogs as $log) {
                fputcsv($file, [
                    $log->batch_number,
                    $log->product->name ?? 'N/A',
                    $log->quantity_produced,
                    $log->production_date,
                    ucfirst($log->status),
                    $log->yield_percentage,
                    $log->quality_grade,
                    $log->waste_quantity,
                    $log->total_production_cost,
                    $log->labor_cost,
                    $log->user->name ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
