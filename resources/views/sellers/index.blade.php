<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Sellers') }}
            </h2>
            @if(Auth::user()->isAdmin())
            <a href="{{ route('sellers.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-plus"></i>
                <span>Add New Seller</span>
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

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6">
                    @if($sellers->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GST Number</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($sellers as $seller)
                                        <tr class="{{ !$seller->is_active ? 'bg-gray-50' : '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $seller->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $seller->company_name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $seller->formatted_gst_number }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div>{{ $seller->phone ?? 'N/A' }}</div>
                                                <div class="text-xs text-gray-400">{{ $seller->email ?? 'N/A' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($seller->is_active)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Active
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Inactive
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="flex items-center justify-center space-x-2">
                                                    <a href="{{ route('sellers.show', $seller) }}"
                                                       class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-full transition-colors"
                                                       title="View">
                                                        <i class="fas fa-eye text-sm"></i>
                                                    </a>
                                                    @if(Auth::user()->isAdmin())
                                                        <a href="{{ route('sellers.edit', $seller) }}"
                                                           class="inline-flex items-center justify-center w-8 h-8 text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50 rounded-full transition-colors"
                                                           title="Edit">
                                                            <i class="fas fa-edit text-sm"></i>
                                                        </a>
                                                        <form action="{{ route('sellers.toggle-status', $seller) }}" method="POST" class="inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit"
                                                                    class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:text-green-900 hover:bg-green-50 rounded-full transition-colors"
                                                                    title="{{ $seller->is_active ? 'Deactivate' : 'Activate' }}">
                                                                <i class="fas fa-{{ $seller->is_active ? 'toggle-on' : 'toggle-off' }} text-sm"></i>
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('sellers.destroy', $seller) }}" method="POST" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-full transition-colors"
                                                                    onclick="return confirm('Are you sure you want to delete this seller?')"
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
                        <div class="text-center py-12">
                            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-handshake text-2xl text-gray-400"></i>
                            </div>
                            <p class="text-gray-500 text-lg mb-4">No sellers found.</p>
                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('sellers.create') }}" class="inline-flex items-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-plus"></i>
                                    <span>Add First Seller</span>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
