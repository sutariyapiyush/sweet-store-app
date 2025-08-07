<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('SweetVedas - Inventory Dashboard') }}
            </h2>
            <div class="text-sm text-gray-600">
                Welcome, {{ Auth::user()->name }} ({{ ucfirst(Auth::user()->role) }})
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">

            <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-64">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="search" value="{{ $search }}"
                                   placeholder="Search materials or products..."
                                   class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <select name="type" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="all" {{ $type === 'all' ? 'selected' : '' }}>All Items</option>
                            <option value="raw_materials" {{ $type === 'raw_materials' ? 'selected' : '' }}>Raw Materials</option>
                            <option value="products" {{ $type === 'products' ? 'selected' : '' }}>Products</option>
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center space-x-2 transition-colors">
                        <i class="fas fa-filter"></i>
                        <span>Filter</span>
                    </button>
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 flex items-center space-x-2 transition-colors">
                        <i class="fas fa-times"></i>
                        <span>Clear</span>
                    </a>
                </form>
            </div>
        </div>

            <!-- Low Stock Alerts -->
            @if($lowStockRawMaterials->count() > 0 || $lowStockProducts->count() > 0)
            <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-red-800 mb-4">⚠️ Low Stock Alerts</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($lowStockRawMaterials->count() > 0)
                    <div>
                        <h4 class="font-medium text-red-700 mb-2">Raw Materials</h4>
                        <ul class="space-y-1">
                            @foreach($lowStockRawMaterials as $material)
                            <li class="text-sm text-red-600">
                                {{ $material->name }}: {{ $material->quantity_in_stock }} {{ $material->unit }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    @if($lowStockProducts->count() > 0)
                    <div>
                        <h4 class="font-medium text-red-700 mb-2">Products</h4>
                        <ul class="space-y-1">
                            @foreach($lowStockProducts as $product)
                            <li class="text-sm text-red-600">
                                {{ $product->name }}: {{ $product->quantity_in_stock }} {{ $product->unit }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Raw Materials -->
                @if($type === 'all' || $type === 'raw_materials')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Raw Materials</h3>
                            @if(Auth::user()->isAdmin())
                            <a href="{{ route('raw-materials.create') }}" class="px-3 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 flex items-center space-x-2 transition-colors">
                                <i class="fas fa-plus"></i>
                                <span>Add New</span>
                            </a>
                            @endif
                        </div>
                        @if($rawMaterials->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($rawMaterials as $material)
                                    <tr class="{{ $material->isLowStock() ? 'bg-red-50' : '' }}">
                                        <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $material->name }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ $material->quantity_in_stock }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ $material->unit }}</td>
                                        <td class="px-4 py-2 text-sm">
                                            @if($material->isLowStock())
                                                <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Low Stock</span>
                                            @else
                                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">In Stock</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p class="text-gray-500 text-center py-4">No raw materials found.</p>
                        @endif
                        <div class="mt-4">
                            <a href="{{ route('raw-materials.index') }}" class="text-blue-600 hover:text-blue-800 text-sm flex items-center space-x-1">
                                <span>View All Raw Materials</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Products -->
                @if($type === 'all' || $type === 'products')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Products</h3>
                            @if(Auth::user()->isAdmin())
                            <a href="{{ route('products.create') }}" class="px-3 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 flex items-center space-x-2 transition-colors">
                                <i class="fas fa-plus"></i>
                                <span>Add New</span>
                            </a>
                            @endif
                        </div>
                        @if($products->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($products as $product)
                                    <tr class="{{ $product->isLowStock() ? 'bg-red-50' : '' }}">
                                        <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ $product->quantity_in_stock }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ $product->unit }}</td>
                                        <td class="px-4 py-2 text-sm">
                                            @if($product->isLowStock())
                                                <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Low Stock</span>
                                            @else
                                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">In Stock</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p class="text-gray-500 text-center py-4">No products found.</p>
                        @endif
                        <div class="mt-4">
                            <a href="{{ route('products.index') }}" class="text-blue-600 hover:text-blue-800 text-sm flex items-center space-x-1">
                                <span>View All Products</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Recent Production -->
            @if($recentProduction->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Production</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($recentProduction as $log)
                                <tr>
                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $log->product->name }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-500">{{ $log->quantity_produced }} {{ $log->product->unit }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-500">{{ $log->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('production-logs.index') }}" class="text-blue-600 hover:text-blue-800 text-sm flex items-center space-x-1">
                            <span>View All Production Logs</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
