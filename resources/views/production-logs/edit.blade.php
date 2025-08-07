<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Production Log') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('production-logs.show', $productionLog) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    View
                </a>
                <a href="{{ route('production-logs.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('production-logs.update', $productionLog) }}">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="product_id" class="block text-sm font-medium text-gray-700">Product *</label>
                                    <select name="product_id" id="product_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ (old('product_id', $productionLog->product_id) == $product->id) ? 'selected' : '' }}>
                                                {{ $product->name }} - {{ $product->sku }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('product_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="batch_number" class="block text-sm font-medium text-gray-700">Batch Number</label>
                                    <input type="text" name="batch_number" id="batch_number" value="{{ old('batch_number', $productionLog->batch_number) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('batch_number')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div>
                                    <label for="quantity_produced" class="block text-sm font-medium text-gray-700">Quantity Produced *</label>
                                    <input type="number" step="0.01" name="quantity_produced" id="quantity_produced" value="{{ old('quantity_produced', $productionLog->quantity_produced) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @error('quantity_produced')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="production_date" class="block text-sm font-medium text-gray-700">Production Date *</label>
                                    <input type="date" name="production_date" id="production_date" value="{{ old('production_date', $productionLog->production_date ? $productionLog->production_date->format('Y-m-d') : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @error('production_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        <option value="">Select Status</option>
                                        <option value="planned" {{ old('status', $productionLog->status) == 'planned' ? 'selected' : '' }}>Planned</option>
                                        <option value="in_progress" {{ old('status', $productionLog->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ old('status', $productionLog->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ old('status', $productionLog->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    @error('status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                                    <input type="time" name="start_time" id="start_time" value="{{ old('start_time', $productionLog->start_time ? $productionLog->start_time->format('H:i') : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('start_time')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                                    <input type="time" name="end_time" id="end_time" value="{{ old('end_time', $productionLog->end_time ? $productionLog->end_time->format('H:i') : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('end_time')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Production Details -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Production Details</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="equipment_used" class="block text-sm font-medium text-gray-700">Equipment Used</label>
                                    <input type="text" name="equipment_used" id="equipment_used" value="{{ old('equipment_used', $productionLog->equipment_used) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Equipment names">
                                    @error('equipment_used')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="staff_assigned" class="block text-sm font-medium text-gray-700">Staff Assigned</label>
                                    <input type="text" name="staff_assigned" id="staff_assigned" value="{{ old('staff_assigned', $productionLog->staff_assigned) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Staff member names">
                                    @error('staff_assigned')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-6">
                                <label for="raw_materials_used" class="block text-sm font-medium text-gray-700">Raw Materials Used (JSON)</label>
                                <textarea name="raw_materials_used" id="raw_materials_used" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder='{"material_1": {"quantity": 100, "unit": "kg"}, "material_2": {"quantity": 50, "unit": "liters"}}'>{{ old('raw_materials_used', $productionLog->raw_materials_used) }}</textarea>
                                @error('raw_materials_used')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Environmental Conditions -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Environmental Conditions</h3>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                                <div>
                                    <label for="temperature" class="block text-sm font-medium text-gray-700">Temperature (°C)</label>
                                    <input type="number" step="0.1" name="temperature" id="temperature" value="{{ old('temperature', $productionLog->temperature) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('temperature')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="humidity" class="block text-sm font-medium text-gray-700">Humidity (%)</label>
                                    <input type="number" step="0.1" name="humidity" id="humidity" value="{{ old('humidity', $productionLog->humidity) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('humidity')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="pressure" class="block text-sm font-medium text-gray-700">Pressure (bar)</label>
                                    <input type="number" step="0.01" name="pressure" id="pressure" value="{{ old('pressure', $productionLog->pressure) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('pressure')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="ph_level" class="block text-sm font-medium text-gray-700">pH Level</label>
                                    <input type="number" step="0.01" name="ph_level" id="ph_level" value="{{ old('ph_level', $productionLog->ph_level) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('ph_level')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Quality & Efficiency Metrics -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Quality & Efficiency Metrics</h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div>
                                    <label for="yield_percentage" class="block text-sm font-medium text-gray-700">Yield Percentage (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" name="yield_percentage" id="yield_percentage" value="{{ old('yield_percentage', $productionLog->yield_percentage) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('yield_percentage')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="efficiency_percentage" class="block text-sm font-medium text-gray-700">Efficiency Percentage (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" name="efficiency_percentage" id="efficiency_percentage" value="{{ old('efficiency_percentage', $productionLog->efficiency_percentage) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('efficiency_percentage')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="quality_score" class="block text-sm font-medium text-gray-700">Quality Score (0-100)</label>
                                    <input type="number" step="0.01" min="0" max="100" name="quality_score" id="quality_score" value="{{ old('quality_score', $productionLog->quality_score) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('quality_score')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="waste_quantity" class="block text-sm font-medium text-gray-700">Waste Quantity</label>
                                    <input type="number" step="0.01" min="0" name="waste_quantity" id="waste_quantity" value="{{ old('waste_quantity', $productionLog->waste_quantity) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('waste_quantity')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="defect_quantity" class="block text-sm font-medium text-gray-700">Defect Quantity</label>
                                    <input type="number" step="0.01" min="0" name="defect_quantity" id="defect_quantity" value="{{ old('defect_quantity', $productionLog->defect_quantity) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('defect_quantity')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Cost & Resource Tracking -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Cost & Resource Tracking</h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div>
                                    <label for="production_cost" class="block text-sm font-medium text-gray-700">Production Cost</label>
                                    <input type="number" step="0.01" min="0" name="production_cost" id="production_cost" value="{{ old('production_cost', $productionLog->production_cost) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('production_cost')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="labor_hours" class="block text-sm font-medium text-gray-700">Labor Hours</label>
                                    <input type="number" step="0.1" min="0" name="labor_hours" id="labor_hours" value="{{ old('labor_hours', $productionLog->labor_hours) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('labor_hours')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="energy_consumption" class="block text-sm font-medium text-gray-700">Energy Consumption (kWh)</label>
                                    <input type="number" step="0.01" min="0" name="energy_consumption" id="energy_consumption" value="{{ old('energy_consumption', $productionLog->energy_consumption) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('energy_consumption')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" id="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Additional notes or observations">{{ old('notes', $productionLog->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('production-logs.show', $productionLog) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Production Log
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
