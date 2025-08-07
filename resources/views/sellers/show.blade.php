<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Seller Details') }}
            </h2>
            @if(Auth::user()->isAdmin())
            <div class="flex space-x-2">
                <a href="{{ route('sellers.edit', $seller) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                    Edit Seller
                </a>
                <a href="{{ route('sellers.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
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
                                    <label class="block text-sm font-medium text-gray-700">Seller Name</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $seller->name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Company Name</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $seller->company_name ?? 'N/A' }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Contact Person</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $seller->contact_person ?? 'N/A' }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $seller->email ?? 'N/A' }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $seller->phone ?? 'N/A' }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status</label>
                                    <p class="mt-1">
                                        @if($seller->is_active)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Active
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Inactive
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Tax Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tax Information</h3>

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">GST Number</label>
                                    <p class="mt-1 text-sm text-gray-900 font-mono">{{ $seller->formatted_gst_number }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">PAN Number</label>
                                    <p class="mt-1 text-sm text-gray-900 font-mono">{{ $seller->pan_number ?? 'N/A' }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Address</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $seller->address ?? 'N/A' }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Created</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $seller->created_at->format('M d, Y') }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $seller->updated_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Raw Materials Section -->
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Associated Raw Materials</h3>

                        @if($seller->rawMaterials->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($seller->rawMaterials as $material)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <a href="{{ route('raw-materials.show', $material) }}" class="text-indigo-600 hover:text-indigo-900">
                                                        {{ $material->name }}
                                                    </a>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $material->unit }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $material->quantity_in_stock }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($material->isLowStock())
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                            Low Stock
                                                        </span>
                                                    @else
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                            In Stock
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <p class="text-gray-500">No raw materials associated with this seller yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
