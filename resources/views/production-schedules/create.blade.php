<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Schedule Production') }}
            </h2>
            <a href="{{ route('production-schedules.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Schedules
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('production-schedules.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Product Selection -->
                            <div>
                                <x-input-label for="product_id" :value="__('Product')" />
                                <select id="product_id" name="product_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}
                                                data-unit="{{ $product->unit }}"
                                                data-production-time="{{ $product->production_time_minutes }}">
                                            {{ $product->name }} ({{ $product->sku ?? 'No SKU' }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('product_id')" class="mt-2" />
                            </div>

                            <!-- Planned Quantity -->
                            <div>
                                <x-input-label for="planned_quantity" :value="__('Planned Quantity')" />
                                <x-text-input id="planned_quantity" class="block mt-1 w-full" type="number" step="0.01" name="planned_quantity" :value="old('planned_quantity')" required />
                                <x-input-error :messages="$errors->get('planned_quantity')" class="mt-2" />
                                <p class="text-sm text-gray-500 mt-1" id="unit-display">Enter quantity to produce</p>
                            </div>

                            <!-- Scheduled Date -->
                            <div>
                                <x-input-label for="scheduled_date" :value="__('Scheduled Date')" />
                                <x-text-input id="scheduled_date" class="block mt-1 w-full" type="date" name="scheduled_date" :value="old('scheduled_date', date('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('scheduled_date')" class="mt-2" />
                            </div>

                            <!-- Scheduled Time -->
                            <div>
                                <x-input-label for="scheduled_time" :value="__('Scheduled Time (Optional)')" />
                                <x-text-input id="scheduled_time" class="block mt-1 w-full" type="time" name="scheduled_time" :value="old('scheduled_time')" />
                                <x-input-error :messages="$errors->get('scheduled_time')" class="mt-2" />
                            </div>

                            <!-- Priority -->
                            <div>
                                <x-input-label for="priority" :value="__('Priority')" />
                                <select id="priority" name="priority" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                <x-input-error :messages="$errors->get('priority')" class="mt-2" />
                            </div>

                            <!-- Assigned User -->
                            <div>
                                <x-input-label for="assigned_user_id" :value="__('Assign To (Optional)')" />
                                <select id="assigned_user_id" name="assigned_user_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Unassigned</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('assigned_user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('assigned_user_id')" class="mt-2" />
                            </div>

                            <!-- Estimated Duration -->
                            <div>
                                <x-input-label for="estimated_duration_minutes" :value="__('Estimated Duration (minutes)')" />
                                <x-text-input id="estimated_duration_minutes" class="block mt-1 w-full" type="number" name="estimated_duration_minutes" :value="old('estimated_duration_minutes')" />
                                <x-input-error :messages="$errors->get('estimated_duration_minutes')" class="mt-2" />
                                <p class="text-sm text-gray-500 mt-1">Leave empty to auto-calculate based on product settings</p>
                            </div>

                            <!-- Due Date -->
                            <div>
                                <x-input-label for="due_date" :value="__('Due Date (Optional)')" />
                                <x-text-input id="due_date" class="block mt-1 w-full" type="date" name="due_date" :value="old('due_date')" />
                                <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-2">
                                <x-input-label for="notes" :value="__('Notes (Optional)')" />
                                <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('production-schedules.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Schedule Production') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productSelect = document.getElementById('product_id');
            const quantityInput = document.getElementById('planned_quantity');
            const unitDisplay = document.getElementById('unit-display');
            const durationInput = document.getElementById('estimated_duration_minutes');

            function updateUnitDisplay() {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                if (selectedOption.value) {
                    const unit = selectedOption.getAttribute('data-unit');
                    unitDisplay.textContent = `Enter quantity in ${unit}`;
                } else {
                    unitDisplay.textContent = 'Enter quantity to produce';
                }
            }

            function updateEstimatedDuration() {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const quantity = parseFloat(quantityInput.value);

                if (selectedOption.value && quantity && !durationInput.value) {
                    const productionTime = parseInt(selectedOption.getAttribute('data-production-time'));
                    if (productionTime) {
                        durationInput.value = Math.round(productionTime * quantity);
                    }
                }
            }

            productSelect.addEventListener('change', function() {
                updateUnitDisplay();
                updateEstimatedDuration();
            });

            quantityInput.addEventListener('input', updateEstimatedDuration);

            // Initialize on page load
            updateUnitDisplay();
        });
    </script>
</x-app-layout>
