<div class="w-64 bg-white shadow-lg h-full flex flex-col">
    <!-- Logo/Brand -->
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-store text-white text-sm"></i>
            </div>
            <div>
                <h1 class="text-lg font-bold text-gray-800">SweetVedas</h1>
                <p class="text-xs text-gray-500">Inventory Management</p>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-4 py-6 space-y-2">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}"
           class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
            <i class="fas fa-tachometer-alt w-5"></i>
            <span>Dashboard</span>
        </a>

        <!-- Raw Materials -->
        <a href="{{ route('raw-materials.index') }}"
           class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('raw-materials.*') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
            <i class="fas fa-boxes w-5"></i>
            <span>Raw Materials</span>
        </a>

        <!-- Sellers -->
        <a href="{{ route('sellers.index') }}"
           class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('sellers.*') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
            <i class="fas fa-handshake w-5"></i>
            <span>Sellers</span>
        </a>

        <!-- Categories -->
        <a href="{{ route('product-categories.index') }}"
           class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('product-categories.*') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
            <i class="fas fa-tags w-5"></i>
            <span>Categories</span>
        </a>

        <!-- Products -->
        <a href="{{ route('products.index') }}"
           class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('products.*') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
            <i class="fas fa-cookie-bite w-5"></i>
            <span>Products</span>
        </a>

        <!-- Production -->
        <a href="{{ route('production-logs.index') }}"
           class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('production-logs.*') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
            <i class="fas fa-industry w-5"></i>
            <span>Production</span>
        </a>

        <!-- Schedules -->
        <a href="{{ route('production-schedules.index') }}"
           class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('production-schedules.*') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
            <i class="fas fa-calendar-alt w-5"></i>
            <span>Schedules</span>
        </a>

        <!-- Quality -->
        <a href="{{ route('quality-checks.index') }}"
           class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('quality-checks.*') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
            <i class="fas fa-check-circle w-5"></i>
            <span>Quality</span>
        </a>

        <!-- Shopify Integration -->
        <div class="pt-4">
            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Shopify Integration</p>
            <div class="mt-2 space-y-1">
                <a href="{{ route('shopify.dashboard') }}"
                   class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('shopify.dashboard') ? 'bg-green-50 text-green-700 border-r-2 border-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <i class="fab fa-shopify w-5"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('shopify.orders.index') }}"
                   class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('shopify.orders.*') ? 'bg-green-50 text-green-700 border-r-2 border-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <i class="fas fa-shopping-cart w-5"></i>
                    <span>Orders</span>
                </a>

                <a href="{{ route('shopify.products.index') }}"
                   class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('shopify.products.*') ? 'bg-green-50 text-green-700 border-r-2 border-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <i class="fas fa-cube w-5"></i>
                    <span>Products</span>
                </a>

            </div>
        </div>

        <!-- Users (Admin Only) -->
        @if(Auth::user()->isAdmin())
        <div class="pt-4">
            <a href="{{ route('users.index') }}"
               class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('users.*') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-users w-5"></i>
                <span>Users</span>
            </a>
        </div>
        @endif
    </nav>

    <!-- User Info -->
    <div class="p-4 border-t border-gray-200">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                <i class="fas fa-user text-gray-600 text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ ucfirst(Auth::user()->role) }}</p>
            </div>
        </div>
    </div>
</div>
