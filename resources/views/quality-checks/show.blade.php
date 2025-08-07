<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Quality Check Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('quality-checks.edit', $qualityCheck) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                    Edit
                </a>
                <a href="{{ route('quality-checks.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Status Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Quality Check Status</h3>
                            <p class="text-sm text-gray-500">{{ $qualityCheck->parameter_name }} - {{ ucfirst($qualityCheck->check_type) }}</p>
                        </div>
                        <div class="text-right">
                            @if($qualityCheck->status === 'pass')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    PASS
                                </span>
                            @elseif($qualityCheck->status === 'fail')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                    FAIL
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    WARNING
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Production Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Production Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Product</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $qualityCheck->productionLog->product->name ?? 'Unknown Product' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Batch Number</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $qualityCheck->productionLog->batch_number ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Production Date</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $qualityCheck->productionLog->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Quantity Produced</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $qualityCheck->productionLog->quantity_produced ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quality Check Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Quality Check Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Check Type</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ ucfirst($qualityCheck->check_type) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Parameter Name</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $qualityCheck->parameter_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Measured Value</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <span class="font-semibold">{{ $qualityCheck->measured_value }}</span>
                                @if($qualityCheck->unit)
                                    <span class="text-gray-500">{{ $qualityCheck->unit }}</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Expected Value</label>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($qualityCheck->expected_value)
                                    {{ $qualityCheck->expected_value }}
                                    @if($qualityCheck->unit)
                                        <span class="text-gray-500">{{ $qualityCheck->unit }}</span>
                                    @endif
                                @else
                                    <span class="text-gray-500">Not specified</span>
                                @endif
                            </p>
                        </div>
                        @if($qualityCheck->tolerance)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tolerance (±)</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $qualityCheck->tolerance }}</p>
                            </div>
                        @endif
                        @if($qualityCheck->min_value || $qualityCheck->max_value)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Acceptable Range</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    @if($qualityCheck->min_value && $qualityCheck->max_value)
                                        {{ $qualityCheck->min_value }} - {{ $qualityCheck->max_value }}
                                    @elseif($qualityCheck->min_value)
                                        Min: {{ $qualityCheck->min_value }}
                                    @elseif($qualityCheck->max_value)
                                        Max: {{ $qualityCheck->max_value }}
                                    @endif
                                    @if($qualityCheck->unit)
                                        <span class="text-gray-500">{{ $qualityCheck->unit }}</span>
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Check Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Check Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Checked By</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $qualityCheck->checked_by }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Check Date & Time</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $qualityCheck->checked_at->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Created</label>
                            <p class="mt-1 text-sm text-gray-500">{{ $qualityCheck->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        @if($qualityCheck->updated_at != $qualityCheck->created_at)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                                <p class="mt-1 text-sm text-gray-500">{{ $qualityCheck->updated_at->format('M d, Y H:i') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Notes and Actions -->
            @if($qualityCheck->notes || $qualityCheck->corrective_action)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Notes & Actions</h3>
                        @if($qualityCheck->notes)
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Notes</label>
                                <div class="mt-1 p-3 bg-gray-50 rounded-md">
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $qualityCheck->notes }}</p>
                                </div>
                            </div>
                        @endif
                        @if($qualityCheck->corrective_action)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Corrective Action</label>
                                <div class="mt-1 p-3 bg-yellow-50 rounded-md border-l-4 border-yellow-400">
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $qualityCheck->corrective_action }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Variance Analysis -->
            @if($qualityCheck->expected_value && $qualityCheck->measured_value)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Variance Analysis</h3>
                        @php
                            $variance = $qualityCheck->measured_value - $qualityCheck->expected_value;
                            $percentageVariance = $qualityCheck->expected_value != 0 ? ($variance / $qualityCheck->expected_value) * 100 : 0;
                        @endphp
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold {{ $variance > 0 ? 'text-red-600' : ($variance < 0 ? 'text-blue-600' : 'text-green-600') }}">
                                    {{ $variance > 0 ? '+' : '' }}{{ number_format($variance, 2) }}
                                </div>
                                <div class="text-sm text-gray-500">Absolute Variance</div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold {{ abs($percentageVariance) > 10 ? 'text-red-600' : (abs($percentageVariance) > 5 ? 'text-yellow-600' : 'text-green-600') }}">
                                    {{ $percentageVariance > 0 ? '+' : '' }}{{ number_format($percentageVariance, 1) }}%
                                </div>
                                <div class="text-sm text-gray-500">Percentage Variance</div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold {{ $qualityCheck->isWithinTolerance() ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $qualityCheck->isWithinTolerance() ? 'YES' : 'NO' }}
                                </div>
                                <div class="text-sm text-gray-500">Within Tolerance</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex space-x-4">
                            <a href="{{ route('quality-checks.edit', $qualityCheck) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                                Edit Check
                            </a>
                            <a href="{{ route('production-logs.show', $qualityCheck->productionLog) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                View Production Log
                            </a>
                        </div>
                        <form action="{{ route('quality-checks.destroy', $qualityCheck) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this quality check?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                Delete Check
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
