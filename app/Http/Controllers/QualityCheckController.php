<?php

namespace App\Http\Controllers;

use App\Models\QualityCheck;
use App\Models\ProductionLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QualityCheckController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = QualityCheck::with(['productionLog.product', 'checker']);

        // Apply filters
        if ($request->filled('production_log_id')) {
            $query->where('production_log_id', $request->production_log_id);
        }

        if ($request->filled('check_type')) {
            $query->where('check_type', $request->check_type);
        }

        if ($request->filled('status')) {
            // Map status filter to result field
            $statusMap = [
                'pass' => 'pass',
                'fail' => 'fail',
                'warning' => 'conditional_pass'
            ];
            if (isset($statusMap[$request->status])) {
                $query->where('result', $statusMap[$request->status]);
            }
        }

        $qualityChecks = $query->ordered()->paginate(15);

        // Get production logs for filter dropdown
        $productionLogs = ProductionLog::with('product')
            ->latest()
            ->get();

        // Calculate statistics using the correct field name 'result'
        $stats = [
            'passed' => QualityCheck::where('result', 'pass')->count(),
            'failed' => QualityCheck::where('result', 'fail')->count(),
            'warnings' => QualityCheck::where('result', 'conditional_pass')->count(),
        ];

        return view('quality-checks.index', compact('qualityChecks', 'productionLogs', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $productionLogs = ProductionLog::with('product')
            ->where('status', 'completed')
            ->latest()
            ->get();

        $users = User::all();

        return view('quality-checks.create', compact('productionLogs', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'production_log_id' => 'required|exists:production_logs,id',
            'check_type' => 'required|in:visual,taste,texture,weight,temperature,packaging,other',
            'result' => 'required|in:pass,fail,conditional_pass',
            'measured_value' => 'nullable|numeric',
            'expected_value' => 'nullable|numeric',
            'tolerance' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'checklist_items' => 'nullable|array',
            'checked_by' => 'required|exists:users,id',
            'corrective_action' => 'nullable|string',
        ]);

        $validated['checked_at'] = now();

        QualityCheck::create($validated);

        return redirect()->route('quality-checks.index')
            ->with('success', 'Quality check recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(QualityCheck $qualityCheck)
    {
        $qualityCheck->load(['productionLog.product', 'checker']);

        return view('quality-checks.show', compact('qualityCheck'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(QualityCheck $qualityCheck)
    {
        $productionLogs = ProductionLog::with('product')
            ->where('status', 'completed')
            ->latest()
            ->get();

        $users = User::all();

        return view('quality-checks.edit', compact('qualityCheck', 'productionLogs', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, QualityCheck $qualityCheck)
    {
        $validated = $request->validate([
            'production_log_id' => 'required|exists:production_logs,id',
            'check_type' => 'required|in:visual,taste,texture,weight,temperature,packaging,other',
            'result' => 'required|in:pass,fail,conditional_pass',
            'measured_value' => 'nullable|numeric',
            'expected_value' => 'nullable|numeric',
            'tolerance' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'checklist_items' => 'nullable|array',
            'checked_by' => 'required|exists:users,id',
            'corrective_action' => 'nullable|string',
        ]);

        $qualityCheck->update($validated);

        return redirect()->route('quality-checks.index')
            ->with('success', 'Quality check updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QualityCheck $qualityCheck)
    {
        $qualityCheck->delete();

        return redirect()->route('quality-checks.index')
            ->with('success', 'Quality check deleted successfully.');
    }

    /**
     * Get quality checks for a specific production log
     */
    public function byProductionLog(ProductionLog $productionLog)
    {
        $qualityChecks = $productionLog->qualityChecks()
            ->with('checker')
            ->ordered()
            ->get();

        return view('quality-checks.by-production-log', compact('qualityChecks', 'productionLog'));
    }

    /**
     * Get failed quality checks
     */
    public function failed()
    {
        $qualityChecks = QualityCheck::with(['productionLog.product', 'checker'])
            ->failed()
            ->ordered()
            ->paginate(15);

        return view('quality-checks.failed', compact('qualityChecks'));
    }

    /**
     * Quality check dashboard/summary
     */
    public function dashboard()
    {
        $recentChecks = QualityCheck::with(['productionLog.product', 'checker'])
            ->recent()
            ->ordered()
            ->take(10)
            ->get();

        $stats = [
            'total_checks' => QualityCheck::count(),
            'passed_checks' => QualityCheck::passed()->count(),
            'failed_checks' => QualityCheck::failed()->count(),
            'conditional_passes' => QualityCheck::conditionalPass()->count(),
        ];

        $checksByType = QualityCheck::selectRaw('check_type, COUNT(*) as count')
            ->groupBy('check_type')
            ->pluck('count', 'check_type')
            ->toArray();

        return view('quality-checks.dashboard', compact('recentChecks', 'stats', 'checksByType'));
    }
}
