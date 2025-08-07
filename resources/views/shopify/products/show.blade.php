<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Product Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('shopify.products.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded flex items-center space-x-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Products</span>
                </a>
                @if(!$product->is_synced_to_local || !$product->localProduct)
                    <button onclick="syncProductToLocal({{ $product->id }})" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded flex items-center space-x-2">
                        <i class="fas fa-sync-alt"></i>
                        <span>Sync to Local</span>
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Product Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Product Information</h3>

                        @if($product->image_url)
                            <div class="mb-6">
                                <img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="w-full h-64 object-cover rounded-lg">
                            </div>
                        @endif

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Title</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $product->title }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Handle</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $product->handle }}</p>
                            </div>

                            @if($product->description)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Description</label>
                                    <div class="mt-1 text-sm text-gray-900 prose max-w-none">
                                        {!! $product->description !!}
                                    </div>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Vendor</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $product->vendor ?? 'N/A' }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Product Type</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $product->product_type ?? 'N/A' }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Price</label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        @if($product->price)
                                            ${{ number_format($product->price, 2) }}
                                        @else
                                            N/A
                                        @endif
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status</label>
                                    <span class="mt-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($product->status === 'active') bg-green-100 text-green-800
                                        @elseif($product->status === 'draft') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($product->status) }}
                                    </span>
                                </div>
                            </div>

                            @if($product->tags)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tags</label>
                                    <div class="mt-1 flex flex-wrap gap-2">
                                        @foreach(explode(',', $product->tags) as $tag)
                                            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                                {{ trim($tag) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sync Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Sync Information</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Shopify ID</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $product->shopify_product_id }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Sync Status</label>
                                <div class="mt-1">
                                    @if($product->is_synced_to_local && $product->localProduct)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Synced to Local
                                        </span>
                                    @elseif($product->sync_errors)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Sync Error
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Needs Sync
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if($product->localProduct)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Local Product</label>
                                    <div class="mt-1">
                                        <a href="{{ route('products.show', $product->localProduct) }}" class="text-blue-600 hover:text-blue-900 flex items-center space-x-1">
                                            <i class="fas fa-link text-xs"></i>
                                            <span>{{ $product->localProduct->name }}</span>
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Created</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $product->created_at->format('M d, Y H:i') }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ $product->shopify_updated_at ? $product->shopify_updated_at->format('M d, Y H:i') : 'N/A' }}
                                    </p>
                                </div>
                            </div>

                            @if($product->last_synced_at)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Last Synced</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $product->last_synced_at->format('M d, Y H:i') }}</p>
                                </div>
                            @endif

                            @if($product->sync_errors)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Sync Errors</label>
                                    <div class="mt-1 p-3 bg-red-50 border border-red-200 rounded-md">
                                        <pre class="text-sm text-red-800 whitespace-pre-wrap">{{ $product->sync_errors }}</pre>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Raw Data -->
            @if($product->shopify_data)
                <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Raw Shopify Data</h3>
                        <div class="bg-gray-50 p-4 rounded-md overflow-x-auto">
                            <pre class="text-sm text-gray-800">{{ json_encode(json_decode($product->shopify_data), JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        function syncProductToLocal(productId) {
            if (confirm('Sync this product to local database?')) {
                fetch(`/shopify/products/${productId}/sync-to-local`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Product synced to local successfully');
                        location.reload();
                    } else {
                        alert('❌ Failed to sync product to local');
                    }
                })
                .catch(error => {
                    alert('❌ Failed to sync product: ' + error.message);
                });
            }
        }
    </script>
</x-app-layout>
