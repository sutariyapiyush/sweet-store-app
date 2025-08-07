<?php

namespace App\Http\Controllers;

use App\Models\RawMaterial;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RawMaterialController extends Controller
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
        $rawMaterials = RawMaterial::with('seller')->orderBy('name')->get();
        return view('raw-materials.index', compact('rawMaterials'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Only admin can create
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only admins can create raw materials.');
        }

        $sellers = Seller::active()->orderBy('name')->get();
        return view('raw-materials.create', compact('sellers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Only admin can store
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only admins can create raw materials.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:raw_materials',
            'unit' => 'required|string|max:50',
            'quantity_in_stock' => 'required|numeric|min:0',
            'seller_id' => 'nullable|exists:sellers,id',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'invoice' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $data = $request->except('invoice');

        // Handle invoice upload
        if ($request->hasFile('invoice')) {
            $invoicePath = $request->file('invoice')->store('invoices', 'public');
            $data['invoice_path'] = $invoicePath;
        }

        RawMaterial::create($data);

        return redirect()->route('raw-materials.index')
                        ->with('success', 'Raw material created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(RawMaterial $rawMaterial)
    {
        $rawMaterial->load('seller');
        return view('raw-materials.show', compact('rawMaterial'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RawMaterial $rawMaterial)
    {
        // Only admin can edit
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only admins can edit raw materials.');
        }

        $sellers = Seller::active()->orderBy('name')->get();
        return view('raw-materials.edit', compact('rawMaterial', 'sellers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RawMaterial $rawMaterial)
    {
        // Only admin can update
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only admins can update raw materials.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:raw_materials,name,' . $rawMaterial->id,
            'unit' => 'required|string|max:50',
            'quantity_in_stock' => 'required|numeric|min:0',
            'seller_id' => 'nullable|exists:sellers,id',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'invoice' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $data = $request->except('invoice');

        // Handle invoice upload
        if ($request->hasFile('invoice')) {
            // Delete old invoice if exists
            if ($rawMaterial->invoice_path) {
                Storage::disk('public')->delete($rawMaterial->invoice_path);
            }

            $invoicePath = $request->file('invoice')->store('invoices', 'public');
            $data['invoice_path'] = $invoicePath;
        }

        $rawMaterial->update($data);

        return redirect()->route('raw-materials.index')
                        ->with('success', 'Raw material updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RawMaterial $rawMaterial)
    {
        // Only admin can delete
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only admins can delete raw materials.');
        }

        // Delete invoice file if exists
        if ($rawMaterial->invoice_path) {
            Storage::disk('public')->delete($rawMaterial->invoice_path);
        }

        $rawMaterial->delete();

        return redirect()->route('raw-materials.index')
                        ->with('success', 'Raw material deleted successfully.');
    }
}
