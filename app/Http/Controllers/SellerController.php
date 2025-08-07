<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sellers = Seller::orderBy('name')->get();
        return view('sellers.index', compact('sellers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Only admin can create
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only admins can create sellers.');
        }

        return view('sellers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Only admin can store
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only admins can create sellers.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:sellers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'gst_number' => 'required|string|size:15|unique:sellers,gst_number',
            'pan_number' => 'nullable|string|size:10',
            'contact_person' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Validate GST number format
        if (!Seller::isValidGstNumber($request->gst_number)) {
            return back()->withErrors(['gst_number' => 'Invalid GST number format.'])->withInput();
        }

        $seller = Seller::create($request->all());

        return redirect()->route('sellers.index')
                        ->with('success', 'Seller created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Seller $seller)
    {
        $seller->load('rawMaterials');
        return view('sellers.show', compact('seller'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Seller $seller)
    {
        // Only admin can edit
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only admins can edit sellers.');
        }

        return view('sellers.edit', compact('seller'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Seller $seller)
    {
        // Only admin can update
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only admins can update sellers.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:sellers,email,' . $seller->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'gst_number' => 'required|string|size:15|unique:sellers,gst_number,' . $seller->id,
            'pan_number' => 'nullable|string|size:10',
            'contact_person' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Validate GST number format
        if (!Seller::isValidGstNumber($request->gst_number)) {
            return back()->withErrors(['gst_number' => 'Invalid GST number format.'])->withInput();
        }

        $seller->update($request->all());

        return redirect()->route('sellers.index')
                        ->with('success', 'Seller updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Seller $seller)
    {
        // Only admin can delete
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only admins can delete sellers.');
        }

        // Check if seller has associated raw materials
        if ($seller->rawMaterials()->count() > 0) {
            return redirect()->route('sellers.index')
                            ->with('error', 'Cannot delete seller with associated raw materials.');
        }

        $seller->delete();

        return redirect()->route('sellers.index')
                        ->with('success', 'Seller deleted successfully.');
    }

    /**
     * Toggle seller active status
     */
    public function toggleStatus(Seller $seller)
    {
        // Only admin can toggle status
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only admins can change seller status.');
        }

        $seller->update(['is_active' => !$seller->is_active]);

        $status = $seller->is_active ? 'activated' : 'deactivated';
        return redirect()->route('sellers.index')
                        ->with('success', "Seller {$status} successfully.");
    }
}
