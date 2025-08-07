<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Raw Materials') }}
            </h2>
            @if(Auth::user()->isAdmin())
            <a href="{{ route('raw-materials.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i>
                <span>Add New Raw Material</span>
            </a>
            @endif
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="w-full">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card">
                <div class="p-6">
                    @if($rawMaterials->count() > 0)
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Unit</th>
                                        <th>Stock</th>
                                        <th>Seller</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rawMaterials as $material)
                                        <tr class="{{ $material->isLowStock() ? 'bg-red-50' : '' }}">
                                            <td class="font-medium text-gray-900">
                                                <div class="flex items-center space-x-2">
                                                    <i class="fas fa-box text-gray-400"></i>
                                                    <span>{{ $material->name }}</span>
                                                </div>
                                            </td>
                                            <td class="text-gray-500">
                                                {{ $material->unit }}
                                            </td>
                                            <td class="text-gray-500">
                                                <div class="flex items-center space-x-1">
                                                    <span>{{ $material->quantity_in_stock }}</span>
                                                    @if($material->isLowStock())
                                                        <i class="fas fa-exclamation-triangle text-red-500 text-xs" title="Low Stock"></i>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-gray-500">
                                                @if($material->seller)
                                                    <a href="{{ route('sellers.show', $material->seller) }}" class="text-blue-600 hover:text-blue-900 flex items-center space-x-1">
                                                        <i class="fas fa-user text-xs"></i>
                                                        <span>{{ $material->seller->name }}</span>
                                                    </a>
                                                @else
                                                    <span class="text-gray-400 flex items-center space-x-1">
                                                        <i class="fas fa-user-slash text-xs"></i>
                                                        <span>No Seller</span>
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($material->isLowStock())
                                                    <span class="status-badge low-stock">
                                                        Low Stock
                                                    </span>
                                                @else
                                                    <span class="status-badge in-stock">
                                                        In Stock
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="flex items-center justify-center space-x-2">
                                                    <a href="{{ route('raw-materials.show', $material) }}"
                                                       class="action-icon-btn view"
                                                       title="View">
                                                        <i class="fas fa-eye text-sm"></i>
                                                    </a>
                                                    @if(Auth::user()->isAdmin())
                                                        <a href="{{ route('raw-materials.edit', $material) }}"
                                                           class="action-icon-btn edit"
                                                           title="Edit">
                                                            <i class="fas fa-edit text-sm"></i>
                                                        </a>
                                                        <form action="{{ route('raw-materials.destroy', $material) }}" method="POST" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="action-icon-btn delete"
                                                                    onclick="return confirm('Are you sure you want to delete this raw material?')"
                                                                    title="Delete">
                                                                <i class="fas fa-trash text-sm"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-boxes text-2xl text-gray-400"></i>
                            </div>
                            <p class="text-gray-500 text-lg mb-4">No raw materials found.</p>
                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('raw-materials.create') }}" class="btn-primary">
                                    <i class="fas fa-plus"></i>
                                    <span>Add First Raw Material</span>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
