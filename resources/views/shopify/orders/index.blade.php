<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Shopify Orders') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="{{ route('shopify.orders.index') }}" class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-64">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Search orders..."
                                   class="w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <div>
                            <select name="status" class="border border-gray-300 rounded-md px-3 py-2">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <select name="financial_status" class="border border-gray-300 rounded-md px-3 py-2">
                                <option value="">All Financial Status</option>
                                <option value="pending" {{ request('financial_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ request('financial_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="refunded" {{ request('financial_status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                        </div>
                        <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Filter
                        </button>
                        <a href="{{ route('shopify.orders.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Clear
                        </a>
                    </form>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if(count($orders) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Financial</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($orders as $order)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $order['name'] }}</div>
                                                <div class="text-sm text-gray-500">#{{ $order['order_number'] }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $customerName = 'N/A';
                                                    if (isset($order['customer']['first_name'])) {
                                                        $customerName = trim($order['customer']['first_name'] . ' ' . ($order['customer']['last_name'] ?? ''));
                                                    }
                                                @endphp
                                                <div class="text-sm text-gray-900">{{ $customerName }}</div>
                                                <div class="text-sm text-gray-500">{{ $order['customer']['email'] ?? '' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ count($order['line_items'] ?? []) }} items
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $order['currency'] }} {{ number_format($order['total_price'], 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $fulfillmentStatus = $order['fulfillment_status'] ?? 'unfulfilled';
                                                    $statusClass = 'bg-gray-100 text-gray-800';
                                                    $statusText = 'Pending';

                                                    if ($fulfillmentStatus === 'fulfilled') {
                                                        $statusClass = 'bg-green-100 text-green-800';
                                                        $statusText = 'Fulfilled';
                                                    } elseif ($fulfillmentStatus === 'partial') {
                                                        $statusClass = 'bg-purple-100 text-purple-800';
                                                        $statusText = 'Partial';
                                                    } elseif ($order['financial_status'] === 'paid') {
                                                        $statusClass = 'bg-blue-100 text-blue-800';
                                                        $statusText = 'Processing';
                                                    }
                                                @endphp
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                                    {{ $statusText }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if($order['financial_status'] === 'paid') bg-green-100 text-green-800
                                                    @elseif($order['financial_status'] === 'pending') bg-yellow-100 text-yellow-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ ucfirst($order['financial_status']) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ \Carbon\Carbon::parse($order['created_at'])->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('shopify.orders.show', $order['id']) }}"
                                                       class="text-blue-600 hover:text-blue-900">View</a>

                                                    @if($order['financial_status'] === 'paid' && ($order['fulfillment_status'] === null || $order['fulfillment_status'] === 'unfulfilled'))
                                                        <button onclick="updateOrderStatus({{ $order['id'] }}, 'shipped')"
                                                                class="text-purple-600 hover:text-purple-900">Ship</button>
                                                    @endif

                                                    @php
                                                        $hasTracking = false;
                                                        if (isset($order['fulfillments']) && count($order['fulfillments']) > 0) {
                                                            foreach ($order['fulfillments'] as $fulfillment) {
                                                                if (!empty($fulfillment['tracking_number'])) {
                                                                    $hasTracking = true;
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                    @endphp
                                                    @if($hasTracking)
                                                        <a href="{{ route('shopify.orders.track', $order['id']) }}" target="_blank"
                                                           class="text-green-600 hover:text-green-900">Track</a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Simple Pagination -->
                        @if($pagination && $pagination['last_page'] > 1)
                        <div class="mt-6">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-700">
                                    Showing {{ $pagination['from'] }} to {{ $pagination['to'] }} of {{ $pagination['total'] }} results
                                </div>
                                <div class="flex space-x-2">
                                    @if($pagination['current_page'] > 1)
                                        <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['current_page'] - 1]) }}"
                                           class="px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Previous</a>
                                    @endif

                                    @if($pagination['current_page'] < $pagination['last_page'])
                                        <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['current_page'] + 1]) }}"
                                           class="px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Next</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-shopping-cart text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500">No orders found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>

        function updateOrderStatus(orderId, status) {
            if (confirm(`Mark order as ${status}?`)) {
                fetch(`{{ url('shopify/orders') }}/${orderId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ status: status })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Order status updated successfully');
                        location.reload();
                    } else {
                        alert('❌ Failed to update order status: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Failed to update order status: ' + error.message);
                });
            }
        }

    </script>
</x-app-layout>
