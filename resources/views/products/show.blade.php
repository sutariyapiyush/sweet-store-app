<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Product Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('products.edit', $product) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                    Edit Product
                </a>
                <a href="{{ route('products.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Products
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Basic Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Product Name</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $product->name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Category</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $product->category ? $product->category->name : 'Uncategorized' }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">SKU</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $product->sku ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Barcode</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $product->barcode ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <p class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->status === 'active' ? 'bg-green-100 text-green-800' : ($product->status === 'inactive' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                                @if($product->is_seasonal)
                                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Seasonal
                                    </span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unit</label>
                            <p class="mt-1 text-sm text-gray-900">{{ ucfirst($product->unit) }}</p>
                        </div>

                        @if($product->description)
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $product->description }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Pricing Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Pricing Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cost Price</label>
                            <p class="mt-1 text-sm text-gray-900">${{ number_format($product->cost_price ?? 0, 2) }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Selling Price</label>
                            <p class="mt-1 text-sm text-gray-900 font-semibold">${{ number_format($product->selling_price ?? 0, 2) }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Wholesale Price</label>
                            <p class="mt-1 text-sm text-gray-900">${{ number_format($product->wholesale_price ?? 0, 2) }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Profit Margin</label>
                            <p class="mt-1 text-sm text-gray-900">{{ number_format($product->getProfitMargin(), 1) }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Inventory Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Current Stock</label>
                            <p class="mt-1 text-sm text-gray-900 {{ $product->needsReorder() ? 'text-red-600 font-semibold' : '' }}">
                                {{ $product->quantity_in_stock }} {{ $product->unit }}
                                @if($product->needsReorder())
                                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Low Stock
                                    </span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Minimum Stock Level</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $product->minimum_stock_level ?? 'Not set' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Maximum Stock Level</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $product->maximum_stock_level ?? 'Not set' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Reorder Quantity</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $product->reorder_quantity ?? 'Not set' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Inventory Value (Cost)</label>
                            <p class="mt-1 text-sm text-gray-900">${{ number_format($product->getInventoryValue(), 2) }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Inventory Value (Selling)</label>
                            <p class="mt-1 text-sm text-gray-900">${{ number_format($product->getInventoryValueAtSellingPrice(), 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Physical Properties -->
            @if($product->weight || $product->shelf_life_days || $product->storage_temperature)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Physical Properties</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @if($product->weight)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Weight</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $product->weight }} kg</p>
                        </div>
                        @endif

                        @if($product->shelf_life_days)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Shelf Life</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $product->shelf_life_days }} days</p>
                        </div>
                        @endif

                        @if($product->storage_temperature)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Storage Temperature</label>
                            <p class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $product->storage_temperature)) }}</p>
                        </div>
                        @endif

                        @if($product->production_time_minutes)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Production Time</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $product->production_time_minutes }} minutes</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Marketing Information -->
            @if($product->ingredients_list || $product->allergens)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Marketing Information</h3>

                    <div class="grid grid-cols-1 gap-6">
                        @if($product->ingredients_list)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ingredients</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $product->ingredients_list }}</p>
                        </div>
                        @endif

                        @if($product->allergens)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Allergens</label>
                            <div class="mt-1 flex flex-wrap gap-2">
                                @foreach($product->allergens as $allergen)
                                    <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">
                                        {{ ucfirst($allergen) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Product Variants -->
            @if($product->variants->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Product Variants ({{ $product->variants->count() }})</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price Modifier</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Final Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($product->variants as $variant)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $variant->variant_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ ucfirst($variant->variant_type) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $variant->price_modifier >= 0 ? '+' : '' }}${{ number_format($variant->price_modifier, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
                                        ${{ number_format($variant->getCalculatedPrice(), 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $variant->getFullSku() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $variant->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $variant->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Raw Materials (BOM) -->
            @if($product->rawMaterials->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Bill of Materials ({{ $product->rawMaterials->count() }} items)</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Raw Material</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Required Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Stock</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($product->rawMaterials as $rawMaterial)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $rawMaterial->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $rawMaterial->pivot->quantity_required }} {{ $rawMaterial->unit }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $rawMaterial->quantity_in_stock }} {{ $rawMaterial->unit }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($rawMaterial->quantity_in_stock >= $rawMaterial->pivot->quantity_required)
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
                </div>
            </div>
            @endif

            <!-- Recent Production History -->
            @if($product->productionLogs->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Recent Production History</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quality</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($product->productionLogs->take(5) as $log)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $log->production_date ? $log->production_date->format('M d, Y') : $log->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $log->batch_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $log->quantity_produced }} {{ $product->unit }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($log->quality_grade)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $log->quality_grade === 'A' ? 'bg-green-100 text-green-800' : ($log->quality_grade === 'B' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                Grade {{ $log->quality_grade }}
                                            </span>
                                        @else
                                            <span class="text-gray-500">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $log->user ? $log->user->name : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $log->status === 'completed' ? 'bg-green-100 text-green-800' : ($log->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($log->status ?? 'completed') }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
