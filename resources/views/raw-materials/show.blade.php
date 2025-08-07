<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Raw Material Details') }}
            </h2>
            @if(Auth::user()->isAdmin())
            <div class="flex space-x-2">
                <a href="{{ route('raw-materials.edit', $rawMaterial) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                    Edit Material
                </a>
                <a href="{{ route('raw-materials.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to List
                </a>
            </div>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column - Basic Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Material Name</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $rawMaterial->name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Unit</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $rawMaterial->unit }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Stock Quantity</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $rawMaterial->quantity_in_stock }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Stock Status</label>
                                    <p class="mt-1">
                                        @if($rawMaterial->isLowStock())
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Low Stock
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                In Stock
                                            </span>
                                        @endif
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Created</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $rawMaterial->created_at->format('M d, Y') }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $rawMaterial->updated_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Seller & Purchase Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Seller & Purchase Information</h3>

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Seller</label>
                                    @if($rawMaterial->seller)
                                        <div class="mt-1">
                                            <a href="{{ route('sellers.show', $rawMaterial->seller) }}" class="text-indigo-600 hover:text-indigo-900">
                                                {{ $rawMaterial->seller->name }}
                                            </a>
                                            <p class="text-xs text-gray-500">{{ $rawMaterial->seller->formatted_gst_number }}</p>
                                        </div>
                                    @else
                                        <p class="mt-1 text-sm text-gray-500">No seller assigned</p>
                                    @endif
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Purchase Date</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $rawMaterial->purchase_date ? $rawMaterial->purchase_date->format('M d, Y') : 'N/A' }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Purchase Price</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $rawMaterial->purchase_price ? '₹' . number_format($rawMaterial->purchase_price, 2) : 'N/A' }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Invoice</label>
                                    @if($rawMaterial->invoice_path)
                                        <div class="mt-1">
                                            <a href="{{ $rawMaterial->invoice_url }}" target="_blank" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                View Invoice
                                            </a>
                                            <p class="text-xs text-gray-500 mt-1">{{ $rawMaterial->invoice_file_name }}</p>
                                        </div>
                                    @else
                                        <p class="mt-1 text-sm text-gray-500">No invoice uploaded</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Using This Material -->
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Products Using This Material</h3>

                        @if($rawMaterial->products->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Required</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($rawMaterial->products as $product)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <a href="{{ route('products.show', $product) }}" class="text-indigo-600 hover:text-indigo-900">
                                                        {{ $product->name }}
                                                    </a>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $product->pivot->quantity_required }} {{ $rawMaterial->unit }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <p class="text-gray-500">This material is not used in any products yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
