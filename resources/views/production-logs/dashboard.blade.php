<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Production Analytics Dashboard') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('production-logs.export') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Export Data
                </a>
                <a href="{{ route('production-logs.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Logs
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Key Metrics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 16a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">Total Production</div>
                                <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_production'] ?? 0) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">Avg Efficiency</div>
                                <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['avg_efficiency'] ?? 0, 1) }}%</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">Completed Batches</div>
                                <div class="text-2xl font-bold text-gray-900">{{ $stats['completed_batches'] ?? 0 }}</div>
                                <div class="text-xs text-gray-500">of {{ $stats['total_batches'] ?? 0 }} total</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">Total Cost</div>
                                <div class="text-2xl font-bold text-gray-900">${{ number_format($stats['total_cost'] ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Production Logs -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Production Logs</h3>
                        <div class="space-y-4">
                            @forelse($recentLogs as $log)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $log->product->name ?? 'Unknown Product' }}</div>
                                        <div class="text-sm text-gray-500">
                                            Batch: {{ $log->batch_number ?? 'N/A' }} •
                                            {{ $log->created_at->format('M d, Y') }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-medium text-gray-900">{{ number_format($log->quantity_produced) }}</div>
                                        @if($log->status === 'completed')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Completed
                                            </span>
                                        @elseif($log->status === 'in_progress')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                In Progress
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ ucfirst($log->status) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 text-center py-4">No recent production logs found.</p>
                            @endforelse
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('production-logs.index') }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                View all production logs →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Production by Product -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Top Products by Volume</h3>
                        <div class="space-y-4">
                            @forelse($productionByProduct as $production)
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">{{ $production->product->name ?? 'Unknown Product' }}</div>
                                        <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                            @php
                                                $maxQuantity = $productionByProduct->first()->total_quantity ?? 1;
                                                $percentage = ($production->total_quantity / $maxQuantity) * 100;
                                            @endphp
                                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                    <div class="ml-4 text-right">
                                        <div class="text-sm font-medium text-gray-900">{{ number_format($production->total_quantity) }}</div>
                                        <div class="text-xs text-gray-500">units</div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 text-center py-4">No production data available.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Efficiency Trends -->
            @if($efficiencyTrends->count() > 0)
                <div class="mt-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Efficiency Trends (Last 30 Days)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-7 gap-2">
                                @foreach($efficiencyTrends as $trend)
                                    <div class="text-center">
                                        <div class="text-xs text-gray-500 mb-1">{{ \Carbon\Carbon::parse($trend->date)->format('M d') }}</div>
                                        <div class="bg-gray-200 rounded-full h-20 w-4 mx-auto relative">
                                            @php
                                                $height = min($trend->avg_efficiency ?? 0, 100);
                                            @endphp
                                            <div class="bg-green-500 rounded-full absolute bottom-0 w-full" style="height: {{ $height }}%"></div>
                                        </div>
                                        <div class="text-xs text-gray-700 mt-1">{{ number_format($trend->avg_efficiency ?? 0, 1) }}%</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Additional Metrics -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-3">Quality Overview</h4>
                        @if(isset($stats['avg_quality_grade']))
                            <div class="text-center">
                                <div class="text-3xl font-bold text-gray-900">Grade {{ $stats['avg_quality_grade'] }}</div>
                                <div class="text-sm text-gray-500">Average Quality</div>
                            </div>
                        @else
                            <p class="text-gray-500 text-center">No quality data available</p>
                        @endif
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-3">Waste Management</h4>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-red-600">{{ number_format($stats['total_waste'] ?? 0) }}</div>
                            <div class="text-sm text-gray-500">Total Waste</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-3">Production Rate</h4>
                        <div class="text-center">
                            @php
                                $completionRate = $stats['total_batches'] > 0 ? ($stats['completed_batches'] / $stats['total_batches']) * 100 : 0;
                            @endphp
                            <div class="text-3xl font-bold text-blue-600">{{ number_format($completionRate, 1) }}%</div>
                            <div class="text-sm text-gray-500">Completion Rate</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
