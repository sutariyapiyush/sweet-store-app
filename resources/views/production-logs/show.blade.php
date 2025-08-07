<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Production Log Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('production-logs.edit', $productionLog) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                    Edit
                </a>
                <a href="{{ route('production-logs.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <!-- Status Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $productionLog->product->name ?? 'Unknown Product' }}</h3>
                            <p class="text-sm text-gray-500">Batch: {{ $productionLog->batch_number ?? 'N/A' }}</p>
                        </div>
                        <div class="text-right">
                            @if($productionLog->status === 'completed')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    COMPLETED
                                </span>
                            @elseif($productionLog->status === 'in_progress')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                    IN PROGRESS
                                </span>
                            @elseif($productionLog->status === 'planned')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                    </svg>
                                    PLANNED
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                    CANCELLED
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Information -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Product</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $productionLog->product->name ?? 'Unknown Product' }}</p>
                                    @if($productionLog->product->sku)
                                        <p class="text-xs text-gray-500">SKU: {{ $productionLog->product->sku }}</p>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Batch Number</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $productionLog->batch_number ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Quantity Produced</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ number_format($productionLog->quantity_produced) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Production Date</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $productionLog->production_date ? $productionLog->production_date->format('M d, Y') : $productionLog->created_at->format('M d, Y') }}</p>
                                </div>
                                @if($productionLog->start_time || $productionLog->end_time)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Production Time</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            @if($productionLog->start_time && $productionLog->end_time)
                                                {{ $productionLog->start_time->format('H:i') }} - {{ $productionLog->end_time->format('H:i') }}
                                                @if($productionLog->duration_minutes)
                                                    <span class="text-gray-500">({{ $productionLog->duration_minutes }} min)</span>
                                                @endif
                                            @elseif($productionLog->start_time)
                                                Started: {{ $productionLog->start_time->format('H:i') }}
                                            @elseif($productionLog->end_time)
                                                Ended: {{ $productionLog->end_time->format('H:i') }}
                                            @endif
                                        </p>
                                    </div>
                                @endif
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Created By</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $productionLog->user->name ?? 'Unknown User' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Production Details -->
                    @if($productionLog->equipment_used || $productionLog->staff_assigned || $productionLog->raw_materials_used)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Production Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    @if($productionLog->equipment_used)
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Equipment Used</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ $productionLog->equipment_used }}</p>
                                        </div>
                                    @endif
                                    @if($productionLog->staff_assigned)
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Staff Assigned</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ $productionLog->staff_assigned }}</p>
                                        </div>
                                    @endif
                                </div>
                                @if($productionLog->raw_materials_used)
                                    <div class="mt-4">
                                        <label class="block text-sm font-medium text-gray-700">Raw Materials Used</label>
                                        <div class="mt-1 p-3 bg-gray-50 rounded-md">
                                            <pre class="text-sm text-gray-900 whitespace-pre-wrap">{{ $productionLog->raw_materials_used }}</pre>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Environmental Conditions -->
                    @if($productionLog->temperature || $productionLog->humidity || $productionLog->pressure || $productionLog->ph_level)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Environmental Conditions</h3>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                    @if($productionLog->temperature)
                                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                                            <div class="text-2xl font-bold text-blue-600">{{ $productionLog->temperature }}°C</div>
                                            <div class="text-sm text-gray-500">Temperature</div>
                                        </div>
                                    @endif
                                    @if($productionLog->humidity)
                                        <div class="text-center p-4 bg-green-50 rounded-lg">
                                            <div class="text-2xl font-bold text-green-600">{{ $productionLog->humidity }}%</div>
                                            <div class="text-sm text-gray-500">Humidity</div>
                                        </div>
                                    @endif
                                    @if($productionLog->pressure)
                                        <div class="text-center p-4 bg-purple-50 rounded-lg">
                                            <div class="text-2xl font-bold text-purple-600">{{ $productionLog->pressure }} bar</div>
                                            <div class="text-sm text-gray-500">Pressure</div>
                                        </div>
                                    @endif
                                    @if($productionLog->ph_level)
                                        <div class="text-center p-4 bg-yellow-50 rounded-lg">
                                            <div class="text-2xl font-bold text-yellow-600">{{ $productionLog->ph_level }}</div>
                                            <div class="text-sm text-gray-500">pH Level</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Notes -->
                    @if($productionLog->notes)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Notes</h3>
                                <div class="p-3 bg-gray-50 rounded-md">
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $productionLog->notes }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Quality & Efficiency Metrics -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Performance Metrics</h3>
                            <div class="space-y-4">
                                @if($productionLog->yield_percentage)
                                    <div>
                                        <div class="flex justify-between text-sm">
                                            <span class="font-medium text-gray-700">Yield</span>
                                            <span class="text-gray-900">{{ number_format($productionLog->yield_percentage, 1) }}%</span>
                                        </div>
                                        <div class="mt-1 bg-gray-200 rounded-full h-2">
                                            @php
                                                $yieldColorClass = $productionLog->yield_percentage >= 95 ? 'bg-green-500' : ($productionLog->yield_percentage >= 85 ? 'bg-yellow-500' : 'bg-red-500');
                                            @endphp
                                            <div class="{{ $yieldColorClass }} h-2 rounded-full" style="width: {{ min($productionLog->yield_percentage, 100) }}%"></div>
                                        </div>
                                    </div>
                                @endif

                                @if($productionLog->quality_grade)
                                    <div>
                                        <div class="flex justify-between text-sm">
                                            <span class="font-medium text-gray-700">Quality Grade</span>
                                            <span class="text-gray-900">Grade {{ $productionLog->quality_grade }}</span>
                                        </div>
                                        <div class="mt-1">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $productionLog->quality_grade === 'A' ? 'bg-green-100 text-green-800' : ($productionLog->quality_grade === 'B' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                Grade {{ $productionLog->quality_grade }}
                                            </span>
                                        </div>
                                    </div>
                                @endif

                                @if($productionLog->production_time_minutes)
                                    <div>
                                        <div class="flex justify-between text-sm">
                                            <span class="font-medium text-gray-700">Production Time</span>
                                            <span class="text-gray-900">{{ $productionLog->production_time_minutes }} min</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Waste & Issues -->
                    @if($productionLog->waste_quantity || $productionLog->issues_encountered)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Waste & Issues</h3>
                                <div class="space-y-3">
                                    @if($productionLog->waste_quantity)
                                        <div class="flex justify-between">
                                            <span class="text-sm font-medium text-gray-700">Waste Quantity</span>
                                            <span class="text-sm text-gray-900">{{ number_format($productionLog->waste_quantity) }}</span>
                                        </div>
                                    @endif
                                    @if($productionLog->issues_encountered)
                                        <div>
                                            <span class="text-sm font-medium text-gray-700">Issues Encountered</span>
                                            <div class="mt-1 p-2 bg-red-50 rounded text-sm text-red-800">{{ $productionLog->issues_encountered }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Cost & Resources -->
                    @if($productionLog->total_production_cost || $productionLog->labor_cost || $productionLog->overhead_cost)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Cost & Resources</h3>
                                <div class="space-y-3">
                                    @if($productionLog->total_production_cost)
                                        <div class="flex justify-between">
                                            <span class="text-sm font-medium text-gray-700">Total Production Cost</span>
                                            <span class="text-sm text-gray-900">${{ number_format($productionLog->total_production_cost, 2) }}</span>
                                        </div>
                                    @endif
                                    @if($productionLog->labor_cost)
                                        <div class="flex justify-between">
                                            <span class="text-sm font-medium text-gray-700">Labor Cost</span>
                                            <span class="text-sm text-gray-900">${{ number_format($productionLog->labor_cost, 2) }}</span>
                                        </div>
                                    @endif
                                    @if($productionLog->overhead_cost)
                                        <div class="flex justify-between">
                                            <span class="text-sm font-medium text-gray-700">Overhead Cost</span>
                                            <span class="text-sm text-gray-900">${{ number_format($productionLog->overhead_cost, 2) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Quality Checks -->
                    @if($productionLog->qualityChecks->count() > 0)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Quality Checks</h3>
                                    <a href="{{ route('quality-checks.create', ['production_log_id' => $productionLog->id]) }}" class="text-sm text-blue-600 hover:text-blue-900">Add Check</a>
                                </div>
                                <div class="space-y-3">
                                    @foreach($productionLog->qualityChecks->take(5) as $check)
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $check->parameter_name }}</div>
                                                <div class="text-xs text-gray-500">{{ ucfirst($check->check_type) }}</div>
                                            </div>
                                            <div class="text-right">
                                                @if($check->status === 'pass')
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Pass</span>
                                                @elseif($check->status === 'fail')
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Fail</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Warning</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($productionLog->qualityChecks->count() > 5)
                                        <div class="text-center">
                                            <a href="{{ route('quality-checks.for-production-log', $productionLog) }}" class="text-sm text-blue-600 hover:text-blue-900">View all {{ $productionLog->qualityChecks->count() }} checks</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="space-y-3">
                                <a href="{{ route('production-logs.edit', $productionLog) }}" class="w-full bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-center block">
                                    Edit Production Log
                                </a>
                                <a href="{{ route('quality-checks.create', ['production_log_id' => $productionLog->id]) }}" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center block">
                                    Add Quality Check
                                </a>
                                <form action="{{ route('production-logs.destroy', $productionLog) }}" method="POST" class="w-full" onsubmit="return confirm('Are you sure you want to delete this production log?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                        Delete Production Log
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
