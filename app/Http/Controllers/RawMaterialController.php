<?php

namespace App\Http\Controllers;

use App\Models\RawMaterial;
use Illuminate\Http\Request;

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
        $rawMaterials = RawMaterial::orderBy('name')->get();
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

        return view('raw-materials.create');
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
        ]);

        RawMaterial::create($request->all());

        return redirect()->route('raw-materials.index')
                        ->with('success', 'Raw material created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(RawMaterial $rawMaterial)
    {
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

        return view('raw-materials.edit', compact('rawMaterial'));
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
        ]);

        $rawMaterial->update($request->all());

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

        $rawMaterial->delete();

        return redirect()->route('raw-materials.index')
                        ->with('success', 'Raw material deleted successfully.');
    }
}
