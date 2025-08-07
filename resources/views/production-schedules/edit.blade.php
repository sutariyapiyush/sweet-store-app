<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Production Schedule') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('production-schedules.show', $productionSchedule) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    View
                </a>
                <a href="{{ route('production-schedules.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('production-schedules.update', $productionSchedule) }}">
                        @csrf
                        @method('PUT')

                        <!-- Product Selection -->
                        <div class="mb-6">
                            <label for="product_id" class="block text-sm font-medium text-gray-700">Product *</label>
                            <select name="product_id" id="product_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ (old('product_id', $productionSchedule->product_id) == $product->id) ? 'selected' : '' }}>
                                        {{ $product->name }} - {{ $product->sku }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Scheduled Date and Time -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="scheduled_date" class="block text-sm font-medium text-gray-700">Scheduled Date *</label>
                                <input type="date" name="scheduled_date" id="scheduled_date" value="{{ old('scheduled_date', $productionSchedule->scheduled_date->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('scheduled_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="scheduled_time" class="block text-sm font-medium text-gray-700">Scheduled Time</label>
                                <input type="time" name="scheduled_time" id="scheduled_time" value="{{ old('scheduled_time', $productionSchedule->scheduled_time ? $productionSchedule->scheduled_time->format('H:i') : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('scheduled_time')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Quantity and Batch -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="planned_quantity" class="block text-sm font-medium text-gray-700">Planned Quantity *</label>
                                <input type="number" step="0.01" name="planned_quantity" id="planned_quantity" value="{{ old('planned_quantity', $productionSchedule->planned_quantity) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('planned_quantity')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="batch_size" class="block text-sm font-medium text-gray-700">Batch Size</label>
                                <input type="number" step="0.01" name="batch_size" id="batch_size" value="{{ old('batch_size', $productionSchedule->batch_size) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('batch_size')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Priority and Status -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700">Priority *</label>
                                <select name="priority" id="priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Select Priority</option>
                                    <option value="low" {{ old('priority', $productionSchedule->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', $productionSchedule->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority', $productionSchedule->priority) == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority', $productionSchedule->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Select Status</option>
                                    <option value="scheduled" {{ old('status', $productionSchedule->status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                    <option value="in_progress" {{ old('status', $productionSchedule->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ old('status', $productionSchedule->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ old('status', $productionSchedule->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    <option value="delayed" {{ old('status', $productionSchedule->status) == 'delayed' ? 'selected' : '' }}>Delayed</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Duration and Resources -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="estimated_duration" class="block text-sm font-medium text-gray-700">Estimated Duration (hours)</label>
                                <input type="number" step="0.5" name="estimated_duration" id="estimated_duration" value="{{ old('estimated_duration', $productionSchedule->estimated_duration) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('estimated_duration')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="assigned_staff" class="block text-sm font-medium text-gray-700">Assigned Staff</label>
                                <input type="text" name="assigned_staff" id="assigned_staff" value="{{ old('assigned_staff', $productionSchedule->assigned_staff) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Staff member names">
                                @error('assigned_staff')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Equipment and Line -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="equipment_needed" class="block text-sm font-medium text-gray-700">Equipment Needed</label>
                                <input type="text" name="equipment_needed" id="equipment_needed" value="{{ old('equipment_needed', $productionSchedule->equipment_needed) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Required equipment">
                                @error('equipment_needed')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="production_line" class="block text-sm font-medium text-gray-700">Production Line</label>
                                <input type="text" name="production_line" id="production_line" value="{{ old('production_line', $productionSchedule->production_line) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Production line assignment">
                                @error('production_line')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Special Instructions -->
                        <div class="mb-6">
                            <label for="special_instructions" class="block text-sm font-medium text-gray-700">Special Instructions</label>
                            <textarea name="special_instructions" id="special_instructions" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Any special instructions or requirements">{{ old('special_instructions', $productionSchedule->special_instructions) }}</textarea>
                            @error('special_instructions')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Quality Requirements -->
                        <div class="mb-6">
                            <label for="quality_requirements" class="block text-sm font-medium text-gray-700">Quality Requirements</label>
                            <textarea name="quality_requirements" id="quality_requirements" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Quality standards and requirements">{{ old('quality_requirements', $productionSchedule->quality_requirements) }}</textarea>
                            @error('quality_requirements')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Completion Details (if applicable) -->
                        @if($productionSchedule->status === 'completed')
                            <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                                <h4 class="text-sm font-medium text-green-800 mb-3">Completion Details</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="actual_start_time" class="block text-sm font-medium text-green-700">Actual Start Time</label>
                                        <input type="datetime-local" name="actual_start_time" id="actual_start_time" value="{{ old('actual_start_time', $productionSchedule->actual_start_time ? $productionSchedule->actual_start_time->format('Y-m-d\TH:i') : '') }}" class="mt-1 block w-full rounded-md border-green-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    </div>
                                    <div>
                                        <label for="actual_end_time" class="block text-sm font-medium text-green-700">Actual End Time</label>
                                        <input type="datetime-local" name="actual_end_time" id="actual_end_time" value="{{ old('actual_end_time', $productionSchedule->actual_end_time ? $productionSchedule->actual_end_time->format('Y-m-d\TH:i') : '') }}" class="mt-1 block w-full rounded-md border-green-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    </div>
                                    <div>
                                        <label for="actual_quantity" class="block text-sm font-medium text-green-700">Actual Quantity Produced</label>
                                        <input type="number" step="0.01" name="actual_quantity" id="actual_quantity" value="{{ old('actual_quantity', $productionSchedule->actual_quantity) }}" class="mt-1 block w-full rounded-md border-green-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    </div>
                                    <div>
                                        <label for="yield_percentage" class="block text-sm font-medium text-green-700">Yield Percentage</label>
                                        <input type="number" step="0.01" name="yield_percentage" id="yield_percentage" value="{{ old('yield_percentage', $productionSchedule->yield_percentage) }}" class="mt-1 block w-full rounded-md border-green-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Notes -->
                        <div class="mb-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Additional notes or comments">{{ old('notes', $productionSchedule->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('production-schedules.show', $productionSchedule) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Schedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-calculate yield percentage
        document.addEventListener('DOMContentLoaded', function() {
            const plannedQuantity = document.getElementById('planned_quantity');
            const actualQuantity = document.getElementById('actual_quantity');
            const yieldPercentage = document.getElementById('yield_percentage');

            function calculateYield() {
                const planned = parseFloat(plannedQuantity.value);
                const actual = parseFloat(actualQuantity.value);

                if (!isNaN(planned) && !isNaN(actual) && planned > 0) {
                    const yield = (actual / planned) * 100;
                    yieldPercentage.value = yield.toFixed(2);
                }
            }

            if (actualQuantity && yieldPercentage) {
                [plannedQuantity, actualQuantity].forEach(input => {
                    input.addEventListener('input', calculateYield);
                });
            }

            // Show/hide completion details based on status
            const statusSelect = document.getElementById('status');
            const completionDetails = document.querySelector('.bg-green-50');

            function toggleCompletionDetails() {
                if (completionDetails) {
                    if (statusSelect.value === 'completed') {
                        completionDetails.style.display = 'block';
                    } else {
                        completionDetails.style.display = 'none';
                    }
                }
            }

            statusSelect.addEventListener('change', toggleCompletionDetails);
            toggleCompletionDetails(); // Initial check
        });
    </script>
</x-app-layout>
