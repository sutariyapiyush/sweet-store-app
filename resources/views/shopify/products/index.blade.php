<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Shopify Products') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="{{ route('shopify.products.index') }}" class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-64">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Search products..."
                                   class="w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <div>
                            <select name="status" class="border border-gray-300 rounded-md px-3 py-2">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </div>
                        <div>
                            <select name="sync_status" class="border border-gray-300 rounded-md px-3 py-2">
                                <option value="">All Sync Status</option>
                                <option value="synced" {{ request('sync_status') === 'synced' ? 'selected' : '' }}>Synced</option>
                                <option value="needs_sync" {{ request('sync_status') === 'needs_sync' ? 'selected' : '' }}>Needs Sync</option>
                                <option value="errors" {{ request('sync_status') === 'errors' ? 'selected' : '' }}>Has Errors</option>
                            </select>
                        </div>
                        <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded flex items-center space-x-2">
                            <i class="fas fa-filter"></i>
                            <span>Filter</span>
                        </button>
                        <a href="{{ route('shopify.products.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded flex items-center space-x-2">
                            <i class="fas fa-times"></i>
                            <span>Clear</span>
                        </a>
                    </form>
                </div>
            </div>

            <!-- Products Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if($products->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sync Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($products as $product)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if($product->image_url)
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <img class="h-10 w-10 rounded-full object-cover" src="{{ $product->image_url }}" alt="{{ $product->title }}">
                                                        </div>
                                                    @endif
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">{{ $product->title }}</div>
                                                        <div class="text-sm text-gray-500">{{ $product->handle }}</div>
                                                        @if($product->localProduct)
                                                            <div class="text-xs text-green-600">Linked to Local Product</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $product->vendor ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @if($product->price)
                                                    ${{ number_format($product->price, 2) }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if($product->status === 'active') bg-green-100 text-green-800
                                                    @elseif($product->status === 'draft') bg-yellow-100 text-yellow-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ ucfirst($product->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($product->is_synced_to_local && $product->localProduct)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Synced
                                                    </span>
                                                @elseif($product->sync_errors)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Error
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Needs Sync
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $product->shopify_updated_at ? $product->shopify_updated_at->format('M d, Y') : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('shopify.products.show', $product) }}"
                                                       class="text-blue-600 hover:text-blue-900 flex items-center space-x-1">
                                                        <i class="fas fa-eye text-xs"></i>
                                                        <span>View</span>
                                                    </a>

                                                    @if(!$product->is_synced_to_local || !$product->localProduct)
                                                        <button onclick="syncProductToLocal({{ $product->id }})"
                                                                class="text-green-600 hover:text-green-900 flex items-center space-x-1">
                                                            <i class="fas fa-sync text-xs"></i>
                                                            <span>Sync</span>
                                                        </button>
                                                    @endif

                                                    @if($product->sync_errors)
                                                        <button onclick="showSyncErrors({{ $product->id }})"
                                                                class="text-red-600 hover:text-red-900 flex items-center space-x-1">
                                                            <i class="fas fa-exclamation-triangle text-xs"></i>
                                                            <span>Errors</span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $products->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-cube text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500">No products found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Sync Errors Modal -->
    <div id="errorsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Sync Errors</h3>
                <div id="errorContent" class="text-sm text-gray-700 max-h-64 overflow-y-auto">
                    <!-- Error content will be loaded here -->
                </div>
                <div class="mt-4 text-center">
                    <button onclick="closeErrorsModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded flex items-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Close</span>
                    </button>
                </div>
            </div>
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

        function showSyncErrors(productId) {
            // Find the product in the current page data to get sync errors
            // This is a simplified version - in a real app, you might fetch this via AJAX
            const product = @json($products->items()).find(p => p.id === productId);
            if (product && product.sync_errors) {
                document.getElementById('errorContent').innerHTML = '<pre>' + product.sync_errors + '</pre>';
                document.getElementById('errorsModal').classList.remove('hidden');
            }
        }

        function closeErrorsModal() {
            document.getElementById('errorsModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
