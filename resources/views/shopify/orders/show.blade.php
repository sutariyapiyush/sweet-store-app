<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Order Details') }} - #{{ $order['order_number'] ?? $order['name'] }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('shopify.orders.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded flex items-center space-x-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Orders</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Order Status and Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Order Status</h3>
                            <div class="flex items-center space-x-4 mt-2">
                                <span class="px-3 py-1 rounded-full text-sm font-medium
                                    @if(($order['fulfillment_status'] ?? 'unfulfilled') === 'unfulfilled') bg-yellow-100 text-yellow-800
                                    @elseif(($order['fulfillment_status'] ?? 'unfulfilled') === 'partial') bg-blue-100 text-blue-800
                                    @elseif(($order['fulfillment_status'] ?? 'unfulfilled') === 'fulfilled') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($order['fulfillment_status'] ?? 'unfulfilled') }}
                                </span>
                                <span class="text-sm text-gray-600">Financial: {{ ucfirst($order['financial_status'] ?? 'pending') }}</span>
                            </div>
                        </div>

                        <!-- Shipping Actions -->
                        <div class="flex flex-col space-y-2">
                            <div class="flex space-x-2">
                                @if(($order['fulfillment_status'] ?? 'unfulfilled') === 'unfulfilled')
                                <button onclick="openShippingModal()"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm flex items-center space-x-2">
                                    <i class="fas fa-shipping-fast"></i>
                                    <span>Ship Order</span>
                                </button>
                                @endif
                                <button onclick="generateShippingLabel()"
                                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm flex items-center space-x-2">
                                    <i class="fas fa-file-alt"></i>
                                    <span>Full Label</span>
                                </button>
                            </div>
                            <div class="flex space-x-2">
                                <a href="{{ route('shopify.orders.print-label', $order['id']) }}" target="_blank"
                                   class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm text-center flex items-center space-x-2">
                                    <i class="fas fa-print"></i>
                                    <span>Print Label</span>
                                </a>
                            </div>
                            @if(!empty($order['fulfillments']) && count($order['fulfillments']) > 0)
                            <div class="mt-2 p-2 bg-green-50 rounded">
                                <div class="text-sm text-green-800">
                                    @php $fulfillment = $order['fulfillments'][0]; @endphp
                                    <strong>Tracking:</strong> {{ $fulfillment['tracking_number'] ?? 'N/A' }}<br>
                                    <strong>Partner:</strong> {{ $fulfillment['tracking_company'] ?? 'N/A' }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Status Update -->
                    <div class="mt-4">
                        <label for="status" class="block text-sm font-medium text-gray-700">Update Status:</label>
                        <div class="mt-1 flex space-x-2">
                            <select id="status" class="block w-48 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="pending" {{ ($order['fulfillment_status'] ?? 'unfulfilled') === 'unfulfilled' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ ($order['fulfillment_status'] ?? 'unfulfilled') === 'partial' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ ($order['fulfillment_status'] ?? 'unfulfilled') === 'fulfilled' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ ($order['fulfillment_status'] ?? 'unfulfilled') === 'fulfilled' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ ($order['cancelled_at'] ?? null) !== null ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            <button onclick="updateOrderStatus()"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center space-x-2">
                                <i class="fas fa-sync-alt"></i>
                                <span>Update</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Customer Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer Information</h3>
                        <div class="space-y-3">
                            <div>
                                <span class="font-medium text-gray-700">Name:</span>
                                <span class="ml-2 text-gray-900">
                                    @php
                                        $customerName = '';
                                        // Try different sources for customer name
                                        if (!empty($order['customer']['default_address']['first_name']) || !empty($order['customer']['default_address']['last_name'])) {
                                            $customerName = trim(($order['customer']['default_address']['first_name'] ?? '') . ' ' . ($order['customer']['default_address']['last_name'] ?? ''));
                                        } elseif (!empty($order['billing_address']['first_name']) || !empty($order['billing_address']['last_name'])) {
                                            $customerName = trim(($order['billing_address']['first_name'] ?? '') . ' ' . ($order['billing_address']['last_name'] ?? ''));
                                        } elseif (!empty($order['shipping_address']['first_name']) || !empty($order['shipping_address']['last_name'])) {
                                            $customerName = trim(($order['shipping_address']['first_name'] ?? '') . ' ' . ($order['shipping_address']['last_name'] ?? ''));
                                        }

                                        if (empty($customerName)) {
                                            $customerName = 'Customer #' . ($order['customer']['id'] ?? 'Unknown');
                                        }
                                    @endphp
                                    {{ $customerName }}
                                </span>
                            </div>
                            @php
                                $customerEmail = $order['customer']['default_address']['email'] ??
                                               $order['email'] ??
                                               $order['contact_email'] ??
                                               $order['customer']['email'] ??
                                               null;
                            @endphp
                            @if($customerEmail)
                            <div>
                                <span class="font-medium text-gray-700">Email:</span>
                                <span class="ml-2 text-gray-900">{{ $customerEmail }}</span>
                            </div>
                            @else
                            <div>
                                <span class="font-medium text-gray-700">Email:</span>
                                <span class="ml-2 text-gray-500 italic">Not provided</span>
                            </div>
                            @endif
                            @php
                                $customerPhone = $order['customer']['default_address']['phone'] ??
                                               $order['phone'] ??
                                               $order['billing_address']['phone'] ??
                                               $order['shipping_address']['phone'] ??
                                               null;
                            @endphp
                            @if($customerPhone)
                            <div>
                                <span class="font-medium text-gray-700">Phone:</span>
                                <span class="ml-2 text-gray-900">{{ $customerPhone }}</span>
                            </div>
                            @else
                            <div>
                                <span class="font-medium text-gray-700">Phone:</span>
                                <span class="ml-2 text-gray-500 italic">Not provided</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Order Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Information</h3>
                        <div class="space-y-3">
                            <div>
                                <span class="font-medium text-gray-700">Order Date:</span>
                                <span class="ml-2 text-gray-900">{{ date('M d, Y g:i A', strtotime($order['created_at'])) }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Shopify Order ID:</span>
                                <span class="ml-2 text-gray-900">{{ $order['id'] }}</span>
                            </div>
                            @if(!empty($order['order_number']))
                            <div>
                                <span class="font-medium text-gray-700">Order Number:</span>
                                <span class="ml-2 text-gray-900">{{ $order['order_number'] }}</span>
                            </div>
                            @endif
                            <div>
                                <span class="font-medium text-gray-700">Total:</span>
                                <span class="ml-2 text-gray-900 font-semibold">{{ $order['currency'] }} {{ number_format($order['total_price'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Addresses -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                <!-- Billing Address -->
                @if(!empty($order['billing_address']))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Billing Address</h3>
                        <div class="text-gray-900">
                            @php $billing = $order['billing_address']; @endphp
                            @if(is_array($billing))
                                <div>{{ $billing['first_name'] ?? '' }} {{ $billing['last_name'] ?? '' }}</div>
                                <div>{{ $billing['address1'] ?? '' }}</div>
                                @if(!empty($billing['address2']))
                                    <div>{{ $billing['address2'] }}</div>
                                @endif
                                <div>{{ $billing['city'] ?? '' }} {{ $billing['zip'] ?? '' }}</div>
                                <div>{{ $billing['province'] ?? '' }}, {{ $billing['country'] ?? '' }}</div>
                                @if(!empty($billing['phone']))
                                    <div class="mt-2">📞 {{ $billing['phone'] }}</div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Shipping Address -->
                @if(!empty($order['shipping_address']))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Shipping Address</h3>
                        <div class="text-gray-900">
                            @php $shipping = $order['shipping_address']; @endphp
                            @if(is_array($shipping))
                                <div>{{ $shipping['first_name'] ?? '' }} {{ $shipping['last_name'] ?? '' }}</div>
                                <div>{{ $shipping['address1'] ?? '' }}</div>
                                @if(!empty($shipping['address2']))
                                    <div>{{ $shipping['address2'] }}</div>
                                @endif
                                <div>{{ $shipping['city'] ?? '' }} {{ $shipping['zip'] ?? '' }}</div>
                                <div>{{ $shipping['province'] ?? '' }}, {{ $shipping['country'] ?? '' }}</div>
                                @if(!empty($shipping['phone']))
                                    <div class="mt-2">📞 {{ $shipping['phone'] }}</div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Order Items -->
            @if(!empty($order['line_items']) && count($order['line_items']) > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Items</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($order['line_items'] as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $item['title'] }}</div>
                                        @if(!empty($item['variant_title']))
                                            <div class="text-sm text-gray-500">{{ $item['variant_title'] }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item['sku'] ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item['quantity'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $order['currency'] }} {{ number_format($item['price'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $order['currency'] }} {{ number_format($item['quantity'] * $item['price'], 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Order Totals -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Totals</h3>
                    <div class="max-w-md ml-auto">
                        <div class="flex justify-between py-2">
                            <span class="text-gray-700">Subtotal:</span>
                            <span class="text-gray-900">{{ $order['currency'] }} {{ number_format($order['subtotal_price'], 2) }}</span>
                        </div>
                        @if(($order['total_tax'] ?? 0) > 0)
                        <div class="flex justify-between py-2">
                            <span class="text-gray-700">Tax:</span>
                            <span class="text-gray-900">{{ $order['currency'] }} {{ number_format($order['total_tax'], 2) }}</span>
                        </div>
                        @endif
                        @if(!empty($order['shipping_lines']) && count($order['shipping_lines']) > 0)
                        <div class="flex justify-between py-2">
                            <span class="text-gray-700">Shipping:</span>
                            <span class="text-gray-900">{{ $order['currency'] }} {{ number_format($order['shipping_lines'][0]['price'], 2) }}</span>
                        </div>
                        @endif
                        @if(($order['total_discounts'] ?? 0) > 0)
                        <div class="flex justify-between py-2">
                            <span class="text-gray-700">Discounts:</span>
                            <span class="text-red-600">-{{ $order['currency'] }} {{ number_format($order['total_discounts'], 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between py-2 border-t border-gray-200 font-semibold text-lg">
                            <span class="text-gray-900">Total:</span>
                            <span class="text-gray-900">{{ $order['currency'] }} {{ number_format($order['total_price'], 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tracking -->
            @if(!empty($order['fulfillments']) && count($order['fulfillments']) > 0 && !empty($order['fulfillments'][0]['tracking_number']))
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Tracking</h3>
                    <div class="flex items-center space-x-6">
                        <div class="flex-1">
                            <div class="mb-2">
                                <span class="font-medium text-gray-700">Public Tracking URL:</span>
                            </div>
                            <div class="bg-gray-100 p-3 rounded border">
                                <code class="text-sm">{{ route('shopify.orders.track', $order['id']) }}</code>
                            </div>
                            <a href="{{ route('shopify.orders.track', $order['id']) }}" target="_blank"
                               class="inline-block mt-2 text-blue-600 hover:text-blue-800 text-sm flex items-center space-x-1">
                                <i class="fas fa-external-link-alt text-xs"></i>
                                <span>View Public Tracking Page</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Shipping Modal -->
    <div id="shippingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Ship Order</h3>
                    <button onclick="closeShippingModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="shippingForm">
                    <div class="mb-4">
                        <label for="shipping_partner" class="block text-sm font-medium text-gray-700 mb-2">
                            Shipping Partner
                        </label>
                        <select id="shipping_partner" name="shipping_partner" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Shipping Partner</option>
                            <option value="Anjani Courier">Anjani Courier</option>
                            <option value="Blue Dart">Blue Dart</option>
                            <option value="DTDC">DTDC</option>
                            <option value="FedEx">FedEx</option>
                            <option value="Delhivery">Delhivery</option>
                            <option value="Ecom Express">Ecom Express</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="tracking_number" class="block text-sm font-medium text-gray-700 mb-2">
                            Tracking Number
                        </label>
                        <div class="flex space-x-2">
                            <input type="text" id="tracking_number" name="tracking_number" required
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Enter tracking number">
                            <button type="button" onclick="scanBarcode()"
                                    class="bg-gray-500 hover:bg-gray-700 text-white px-3 py-2 rounded-md">
                                <i class="fas fa-barcode"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">You can enter manually or use barcode scanner</p>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeShippingModal()"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                            Ship Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript for AJAX operations -->
    <script>
        function updateOrderStatus() {
            const status = document.getElementById('status').value;

            fetch(`{{ url('shopify/orders') }}/{{ $order['id'] }}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Order status updated successfully!');
                    location.reload();
                } else {
                    alert('Failed to update order status: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the order status.');
            });
        }


        function generateShippingLabel() {
            fetch(`{{ route('shopify.orders.generate-shipping-label', $order['id']) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Shipping label generated successfully!');
                    // Optionally open print window
                    window.open(data.print_url, '_blank');
                } else {
                    alert('Failed to generate shipping label: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while generating the shipping label.');
            });
        }

        function openShippingModal() {
            document.getElementById('shippingModal').classList.remove('hidden');
        }

        function closeShippingModal() {
            document.getElementById('shippingModal').classList.add('hidden');
            document.getElementById('shippingForm').reset();
        }

        function scanBarcode() {
            // Simple implementation - in a real app, you'd integrate with a barcode scanner library
            const trackingInput = document.getElementById('tracking_number');
            const scannedValue = prompt('Scan or enter barcode value:');
            if (scannedValue) {
                trackingInput.value = scannedValue;
            }
        }

        // Handle shipping form submission
        document.getElementById('shippingForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = {
                tracking_number: formData.get('tracking_number'),
                shipping_partner: formData.get('shipping_partner')
            };

            fetch(`{{ url('shopify/orders') }}/{{ $order['id'] }}/ship`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Order shipped successfully and updated in Shopify!');
                    closeShippingModal();
                    location.reload();
                } else {
                    alert('Failed to ship order: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while shipping the order.');
            });
        });
    </script>
</x-app-layout>
