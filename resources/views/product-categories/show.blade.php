<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Product Category Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('product-categories.edit', $productCategory) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                    Edit Category
                </a>
                <a href="{{ route('product-categories.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Categories
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Category Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Category Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $productCategory->name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Slug</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $productCategory->slug }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Parent Category</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $productCategory->parent ? $productCategory->parent->name : 'Root Category' }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <p class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $productCategory->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $productCategory->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sort Order</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $productCategory->sort_order }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Full Path</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $productCategory->getFullPath() }}</p>
                        </div>

                        @if($productCategory->description)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $productCategory->description }}</p>
                        </div>
                        @endif

                        @if($productCategory->image_path)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Image Path</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $productCategory->image_path }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Subcategories -->
            @if($productCategory->children->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Subcategories ({{ $productCategory->children->count() }})</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($productCategory->children as $child)
                        <div class="border rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium">{{ $child->name }}</h4>
                                    <p class="text-sm text-gray-500">{{ $child->products->count() }} products</p>
                                </div>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $child->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $child->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="mt-2">
                                <a href="{{ route('product-categories.show', $child) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">View Details</a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Products in this Category -->
            @if($productCategory->products->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Products in this Category ({{ $productCategory->products->count() }})</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($productCategory->products as $product)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $product->sku ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($product->selling_price ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $product->quantity_in_stock }} {{ $product->unit }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($product->status ?? 'active') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('products.show', $product) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 text-center">
                    <p class="text-gray-500">No products found in this category.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
