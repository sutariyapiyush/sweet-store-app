<?php

namespace App\Http\Controllers;

use App\Models\ProductionSchedule;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProductionScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schedules = ProductionSchedule::with(['product', 'assignedUser'])
            ->upcoming()
            ->ordered()
            ->paginate(15);

        return view('production-schedules.index', compact('schedules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::active()->get();
        $users = User::all();

        return view('production-schedules.create', compact('products', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'nullable|date_format:H:i',
            'planned_quantity' => 'required|numeric|min:0.01',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'estimated_duration_minutes' => 'nullable|integer|min:1',
            'due_date' => 'nullable|date|after_or_equal:scheduled_date',
        ]);

        // Calculate required materials
        $product = Product::with('rawMaterials')->find($validated['product_id']);
        $requiredMaterials = [];

        foreach ($product->rawMaterials as $rawMaterial) {
            $requiredQuantity = $rawMaterial->pivot->quantity_required * $validated['planned_quantity'];
            $requiredMaterials[] = [
                'raw_material_id' => $rawMaterial->id,
                'name' => $rawMaterial->name,
                'required_quantity' => $requiredQuantity,
                'unit' => $rawMaterial->unit,
            ];
        }

        $validated['required_materials'] = $requiredMaterials;

        // Set default estimated duration if not provided
        if (!$validated['estimated_duration_minutes'] && $product->production_time_minutes) {
            $validated['estimated_duration_minutes'] = $product->production_time_minutes * $validated['planned_quantity'];
        }

        ProductionSchedule::create($validated);

        return redirect()->route('production-schedules.index')
            ->with('success', 'Production schedule created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductionSchedule $productionSchedule)
    {
        $productionSchedule->load(['product.rawMaterials', 'assignedUser']);

        return view('production-schedules.show', compact('productionSchedule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductionSchedule $productionSchedule)
    {
        $products = Product::active()->get();
        $users = User::all();

        return view('production-schedules.edit', compact('productionSchedule', 'products', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductionSchedule $productionSchedule)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'nullable|date_format:H:i',
            'planned_quantity' => 'required|numeric|min:0.01',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled,delayed',
            'assigned_user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'estimated_duration_minutes' => 'nullable|integer|min:1',
            'due_date' => 'nullable|date|after_or_equal:scheduled_date',
        ]);

        // Recalculate required materials if product or quantity changed
        if ($validated['product_id'] != $productionSchedule->product_id ||
            $validated['planned_quantity'] != $productionSchedule->planned_quantity) {

            $product = Product::with('rawMaterials')->find($validated['product_id']);
            $requiredMaterials = [];

            foreach ($product->rawMaterials as $rawMaterial) {
                $requiredQuantity = $rawMaterial->pivot->quantity_required * $validated['planned_quantity'];
                $requiredMaterials[] = [
                    'raw_material_id' => $rawMaterial->id,
                    'name' => $rawMaterial->name,
                    'required_quantity' => $requiredQuantity,
                    'unit' => $rawMaterial->unit,
                ];
            }

            $validated['required_materials'] = $requiredMaterials;
        }

        $productionSchedule->update($validated);

        return redirect()->route('production-schedules.index')
            ->with('success', 'Production schedule updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductionSchedule $productionSchedule)
    {
        // Only allow deletion if not in progress or completed
        if (in_array($productionSchedule->status, ['in_progress', 'completed'])) {
            return redirect()->route('production-schedules.index')
                ->with('error', 'Cannot delete a production schedule that is in progress or completed.');
        }

        $productionSchedule->delete();

        return redirect()->route('production-schedules.index')
            ->with('success', 'Production schedule deleted successfully.');
    }

    /**
     * Update the status of a production schedule
     */
    public function updateStatus(Request $request, ProductionSchedule $productionSchedule)
    {
        $validated = $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,cancelled,delayed',
        ]);

        $productionSchedule->update($validated);

        return redirect()->back()
            ->with('success', 'Production schedule status updated successfully.');
    }

    /**
     * Get today's production schedules
     */
    public function today()
    {
        $schedules = ProductionSchedule::with(['product', 'assignedUser'])
            ->today()
            ->ordered()
            ->get();

        return view('production-schedules.today', compact('schedules'));
    }

    /**
     * Get overdue production schedules
     */
    public function overdue()
    {
        $schedules = ProductionSchedule::with(['product', 'assignedUser'])
            ->overdue()
            ->ordered()
            ->get();

        return view('production-schedules.overdue', compact('schedules'));
    }
}
