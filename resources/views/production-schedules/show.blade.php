<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Production Schedule Details') }}
            </h2>
            <div class="flex space-x-2">
                @if(!in_array($productionSchedule->status, ['completed', 'cancelled']))
                    <a href="{{ route('production-schedules.edit', $productionSchedule) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                        Edit Schedule
                    </a>
                @endif
                <a href="{{ route('production-schedules.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Schedules
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Schedule Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Schedule Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Product</label>
                            <p class="mt-1 text-sm text-gray-900 font-medium">{{ $productionSchedule->product->name }}</p>
                            <p class="text-xs text-gray-500">SKU: {{ $productionSchedule->product->sku ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Planned Quantity</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $productionSchedule->planned_quantity }} {{ $productionSchedule->product->unit }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Scheduled Date & Time</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $productionSchedule->scheduled_date->format('M d, Y') }}</p>
                            @if($productionSchedule->scheduled_time)
                                <p class="text-xs text-gray-500">{{ $productionSchedule->scheduled_time->format('H:i') }}</p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Priority</label>
                            <p class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{
                                    $productionSchedule->priority === 'urgent' ? 'bg-red-100 text-red-800' :
                                    ($productionSchedule->priority === 'high' ? 'bg-orange-100 text-orange-800' :
                                    ($productionSchedule->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'))
                                }}">
                                    {{ ucfirst($productionSchedule->priority) }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <p class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{
                                    $productionSchedule->status === 'completed' ? 'bg-green-100 text-green-800' :
                                    ($productionSchedule->status === 'in_progress' ? 'bg-blue-100 text-blue-800' :
                                    ($productionSchedule->status === 'cancelled' ? 'bg-red-100 text-red-800' :
                                    ($productionSchedule->status === 'delayed' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')))
                                }}">
                                    {{ ucfirst(str_replace('_', ' ', $productionSchedule->status)) }}
                                </span>
                                @if($productionSchedule->isOverdue())
                                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Overdue
                                    </span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Assigned To</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $productionSchedule->assignedUser ? $productionSchedule->assignedUser->name : 'Unassigned' }}</p>
                        </div>

                        @if($productionSchedule->estimated_duration_minutes)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Estimated Duration</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $productionSchedule->estimated_duration_minutes }} minutes</p>
                            @if($productionSchedule->getEstimatedCompletionTime())
                                <p class="text-xs text-gray-500">Est. completion: {{ $productionSchedule->getEstimatedCompletionTime()->format('H:i') }}</p>
                            @endif
                        </div>
                        @endif

                        @if($productionSchedule->due_date)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Due Date</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $productionSchedule->due_date->format('M d, Y') }}</p>
                        </div>
                        @endif

                        @if($productionSchedule->notes)
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $productionSchedule->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Required Materials -->
            @if($productionSchedule->required_materials && count($productionSchedule->required_materials) > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Required Materials</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Required Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Stock</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($productionSchedule->calculateRequiredMaterials() as $material)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $material['name'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($material['required_quantity'], 2) }} {{ $material['unit'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($material['available_quantity'], 2) }} {{ $material['unit'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($material['sufficient'])
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Available
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Insufficient
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 p-4 {{ $productionSchedule->hasSufficientMaterials() ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }} rounded-lg">
                        @if($productionSchedule->hasSufficientMaterials())
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-green-800 font-medium">All required materials are available for production.</span>
                            </div>
                        @else
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-red-800 font-medium">Some required materials are insufficient. Please restock before production.</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Status Update Form -->
            @if(!in_array($productionSchedule->status, ['completed', 'cancelled']))
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Update Status</h3>

                    <form action="{{ route('production-schedules.update-status', $productionSchedule) }}" method="POST" class="flex items-end space-x-4">
                        @csrf
                        @method('PATCH')

                        <div class="flex-1">
                            <x-input-label for="status" :value="__('New Status')" />
                            <select id="status" name="status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="scheduled" {{ $productionSchedule->status == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="in_progress" {{ $productionSchedule->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $productionSchedule->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="delayed" {{ $productionSchedule->status == 'delayed' ? 'selected' : '' }}>Delayed</option>
                                <option value="cancelled" {{ $productionSchedule->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <x-primary-button>
                            {{ __('Update Status') }}
                        </x-primary-button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
