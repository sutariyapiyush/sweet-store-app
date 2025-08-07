<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Quality Check') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('quality-checks.show', $qualityCheck) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    View
                </a>
                <a href="{{ route('quality-checks.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('quality-checks.update', $qualityCheck) }}">
                        @csrf
                        @method('PUT')

                        <!-- Production Log Selection -->
                        <div class="mb-6">
                            <label for="production_log_id" class="block text-sm font-medium text-gray-700">Production Log *</label>
                            <select name="production_log_id" id="production_log_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Select Production Log</option>
                                @foreach($productionLogs as $log)
                                    <option value="{{ $log->id }}" {{ (old('production_log_id', $qualityCheck->production_log_id) == $log->id) ? 'selected' : '' }}>
                                        {{ $log->product->name ?? 'Unknown Product' }} - Batch: {{ $log->batch_number ?? 'N/A' }} ({{ $log->created_at->format('M d, Y') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('production_log_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Check Type -->
                        <div class="mb-6">
                            <label for="check_type" class="block text-sm font-medium text-gray-700">Check Type *</label>
                            <select name="check_type" id="check_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Select Check Type</option>
                                <option value="visual" {{ old('check_type', $qualityCheck->check_type) == 'visual' ? 'selected' : '' }}>Visual Inspection</option>
                                <option value="weight" {{ old('check_type', $qualityCheck->check_type) == 'weight' ? 'selected' : '' }}>Weight Check</option>
                                <option value="dimension" {{ old('check_type', $qualityCheck->check_type) == 'dimension' ? 'selected' : '' }}>Dimension Check</option>
                                <option value="taste" {{ old('check_type', $qualityCheck->check_type) == 'taste' ? 'selected' : '' }}>Taste Test</option>
                                <option value="texture" {{ old('check_type', $qualityCheck->check_type) == 'texture' ? 'selected' : '' }}>Texture Check</option>
                                <option value="temperature" {{ old('check_type', $qualityCheck->check_type) == 'temperature' ? 'selected' : '' }}>Temperature Check</option>
                                <option value="ph" {{ old('check_type', $qualityCheck->check_type) == 'ph' ? 'selected' : '' }}>pH Test</option>
                                <option value="moisture" {{ old('check_type', $qualityCheck->check_type) == 'moisture' ? 'selected' : '' }}>Moisture Content</option>
                                <option value="other" {{ old('check_type', $qualityCheck->check_type) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('check_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Parameter Name -->
                        <div class="mb-6">
                            <label for="parameter_name" class="block text-sm font-medium text-gray-700">Parameter Name *</label>
                            <input type="text" name="parameter_name" id="parameter_name" value="{{ old('parameter_name', $qualityCheck->parameter_name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g., Weight, Color, Sweetness Level" required>
                            @error('parameter_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Measured Value and Unit -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="measured_value" class="block text-sm font-medium text-gray-700">Measured Value *</label>
                                <input type="number" step="0.01" name="measured_value" id="measured_value" value="{{ old('measured_value', $qualityCheck->measured_value) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('measured_value')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="unit" class="block text-sm font-medium text-gray-700">Unit</label>
                                <input type="text" name="unit" id="unit" value="{{ old('unit', $qualityCheck->unit) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g., g, cm, °C, pH">
                                @error('unit')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Expected Value and Tolerance -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="expected_value" class="block text-sm font-medium text-gray-700">Expected Value</label>
                                <input type="number" step="0.01" name="expected_value" id="expected_value" value="{{ old('expected_value', $qualityCheck->expected_value) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('expected_value')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="tolerance" class="block text-sm font-medium text-gray-700">Tolerance (±)</label>
                                <input type="number" step="0.01" name="tolerance" id="tolerance" value="{{ old('tolerance', $qualityCheck->tolerance) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('tolerance')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Min and Max Values -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="min_value" class="block text-sm font-medium text-gray-700">Minimum Value</label>
                                <input type="number" step="0.01" name="min_value" id="min_value" value="{{ old('min_value', $qualityCheck->min_value) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('min_value')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="max_value" class="block text-sm font-medium text-gray-700">Maximum Value</label>
                                <input type="number" step="0.01" name="max_value" id="max_value" value="{{ old('max_value', $qualityCheck->max_value) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('max_value')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="mb-6">
                            <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                            <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Select Status</option>
                                <option value="pass" {{ old('status', $qualityCheck->status) == 'pass' ? 'selected' : '' }}>Pass</option>
                                <option value="fail" {{ old('status', $qualityCheck->status) == 'fail' ? 'selected' : '' }}>Fail</option>
                                <option value="warning" {{ old('status', $qualityCheck->status) == 'warning' ? 'selected' : '' }}>Warning</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Checked By -->
                        <div class="mb-6">
                            <label for="checked_by" class="block text-sm font-medium text-gray-700">Checked By *</label>
                            <input type="text" name="checked_by" id="checked_by" value="{{ old('checked_by', $qualityCheck->checked_by) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @error('checked_by')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Check Date and Time -->
                        <div class="mb-6">
                            <label for="checked_at" class="block text-sm font-medium text-gray-700">Check Date & Time *</label>
                            <input type="datetime-local" name="checked_at" id="checked_at" value="{{ old('checked_at', $qualityCheck->checked_at->format('Y-m-d\TH:i')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @error('checked_at')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="mb-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" id="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Additional observations or comments">{{ old('notes', $qualityCheck->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Corrective Action -->
                        <div class="mb-6">
                            <label for="corrective_action" class="block text-sm font-medium text-gray-700">Corrective Action</label>
                            <textarea name="corrective_action" id="corrective_action" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Actions taken if check failed">{{ old('corrective_action', $qualityCheck->corrective_action) }}</textarea>
                            @error('corrective_action')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('quality-checks.show', $qualityCheck) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Quality Check
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-calculate status based on values
        document.addEventListener('DOMContentLoaded', function() {
            const measuredValue = document.getElementById('measured_value');
            const expectedValue = document.getElementById('expected_value');
            const tolerance = document.getElementById('tolerance');
            const minValue = document.getElementById('min_value');
            const maxValue = document.getElementById('max_value');
            const status = document.getElementById('status');

            function calculateStatus() {
                const measured = parseFloat(measuredValue.value);
                const expected = parseFloat(expectedValue.value);
                const tol = parseFloat(tolerance.value);
                const min = parseFloat(minValue.value);
                const max = parseFloat(maxValue.value);

                if (isNaN(measured)) return;

                let newStatus = 'pass';

                // Check against min/max values first
                if (!isNaN(min) && measured < min) {
                    newStatus = 'fail';
                } else if (!isNaN(max) && measured > max) {
                    newStatus = 'fail';
                }
                // Check against expected value with tolerance
                else if (!isNaN(expected) && !isNaN(tol)) {
                    const diff = Math.abs(measured - expected);
                    if (diff > tol) {
                        newStatus = diff > (tol * 1.5) ? 'fail' : 'warning';
                    }
                }

                status.value = newStatus;
            }

            [measuredValue, expectedValue, tolerance, minValue, maxValue].forEach(input => {
                input.addEventListener('input', calculateStatus);
            });
        });
    </script>
</x-app-layout>
