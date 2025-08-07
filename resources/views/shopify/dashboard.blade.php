<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Shopify Integration Dashboard') }}
            </h2>
            <div class="flex space-x-2">
                <button onclick="testConnection()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Test Connection
                </button>
                <button onclick="loadProducts()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Load Products
                </button>
                <button onclick="loadOrders()" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                    Load Orders
                </button>
                <button onclick="setupWebhooks()" class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded">
                    Setup Webhooks
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Connection Status -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Connection Status</h3>
                    @if($shopInfo)
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-400 rounded-full mr-2"></div>
                                <span class="text-sm text-gray-600">Connected to {{ $shopInfo['name'] ?? 'Shopify Store' }}</span>
                            </div>
                            <div class="text-sm text-gray-500">
                                Domain: {{ $shopInfo['domain'] ?? config('shopify.shop_domain') }}
                            </div>
                        </div>
                    @else
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-red-400 rounded-full mr-2"></div>
                            <span class="text-sm text-gray-600">Not connected to Shopify</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Products Stats -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-cube text-blue-500 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">Total Products</div>
                                <div class="text-2xl font-bold text-gray-900">{{ $productStats['total_shopify_products'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="text-sm text-gray-600">
                                Synced: {{ $productStats['synced_to_local'] ?? 0 }} |
                                Needs Sync: {{ $productStats['needs_sync'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orders Stats -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-shopping-cart text-green-500 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">Total Orders</div>
                                <div class="text-2xl font-bold text-gray-900">{{ $orderStats['total_orders'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="text-sm text-gray-600">
                                Pending: {{ $orderStats['pending_orders'] ?? 0 }} |
                                Processing: {{ $orderStats['processing_orders'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipped Orders -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-truck text-yellow-500 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">Shipped Orders</div>
                                <div class="text-2xl font-bold text-gray-900">{{ $orderStats['shipped_orders'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="text-sm text-gray-600">
                                Delivered: {{ $orderStats['delivered_orders'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-clock text-purple-500 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">Recent Orders</div>
                                <div class="text-2xl font-bold text-gray-900">{{ $orderStats['recent_orders'] ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="text-sm text-gray-600">
                                Last 30 days
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Recent Orders</h3>
                        <a href="{{ route('shopify.orders.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                            View All Orders →
                        </a>
                    </div>

                    @if($recentOrders->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentOrders as $order)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $order->name }}</div>
                                                <div class="text-sm text-gray-500">#{{ $order->order_number }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $order->customer_name ?? 'N/A' }}</div>
                                                <div class="text-sm text-gray-500">{{ $order->customer_email }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $order->formatted_total }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if($order->internal_status === 'pending') bg-yellow-100 text-yellow-800
                                                    @elseif($order->internal_status === 'processing') bg-blue-100 text-blue-800
                                                    @elseif($order->internal_status === 'shipped') bg-purple-100 text-purple-800
                                                    @elseif($order->internal_status === 'delivered') bg-green-100 text-green-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ ucfirst($order->internal_status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $order->created_at->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('shopify.orders.show', $order) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                                @if($order->qr_code_path)
                                                    <button onclick="showQrCode('{{ asset('storage/' . $order->qr_code_path) }}')" class="text-green-600 hover:text-green-900">QR</button>
                                                @else
                                                    <button onclick="generateQrCode({{ $order->id }})" class="text-gray-600 hover:text-gray-900">Generate QR</button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-shopping-cart text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500">No recent orders found. Try syncing orders from Shopify.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- QR Code Modal -->
    <div id="qrModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900">Order QR Code</h3>
                <div class="mt-4">
                    <img id="qrImage" src="" alt="QR Code" class="mx-auto">
                </div>
                <div class="mt-4">
                    <button onclick="closeQrModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function testConnection() {
            fetch('{{ route("shopify.test-connection") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Successfully connected to Shopify!');
                        location.reload();
                    } else {
                        alert('❌ Failed to connect to Shopify: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('❌ Connection test failed: ' + error.message);
                });
        }

        function loadProducts() {
            if (confirm('This will load products directly from Shopify. Continue?')) {
                fetch('{{ route("shopify.api.products") }}', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`✅ Products loaded successfully!\nTotal: ${data.count} products found`);
                        // Redirect to products page to show the loaded products
                        window.location.href = '{{ route("shopify.products.index") }}';
                    } else {
                        alert('❌ Failed to load products: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('❌ Failed to load products: ' + error.message);
                });
            }
        }

        function loadOrders() {
            if (confirm('This will load orders directly from Shopify. Continue?')) {
                fetch('{{ route("shopify.api.orders") }}', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`✅ Orders loaded successfully!\nTotal: ${data.count} orders found`);
                        // Redirect to orders page to show the loaded orders
                        window.location.href = '{{ route("shopify.orders.index") }}';
                    } else {
                        alert('❌ Failed to load orders: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('❌ Failed to load orders: ' + error.message);
                });
            }
        }

        function generateQrCode(orderId) {
            fetch(`/shopify/orders/${orderId}/generate-qr`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showQrCode(data.qr_url);
                } else {
                    alert('❌ Failed to generate QR code');
                }
            })
            .catch(error => {
                alert('❌ Failed to generate QR code: ' + error.message);
            });
        }

        function showQrCode(qrUrl) {
            document.getElementById('qrImage').src = qrUrl;
            document.getElementById('qrModal').classList.remove('hidden');
        }

        function closeQrModal() {
            document.getElementById('qrModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
