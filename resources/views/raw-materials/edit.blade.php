<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Raw Material') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('raw-materials.update', $rawMaterial) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $rawMaterial->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="unit" :value="__('Unit (kg, liter, pieces, etc.)')" />
                            <x-text-input id="unit" class="block mt-1 w-full" type="text" name="unit" :value="old('unit', $rawMaterial->unit)" required />
                            <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="quantity_in_stock" :value="__('Stock Quantity')" />
                            <x-text-input id="quantity_in_stock" class="block mt-1 w-full" type="number" step="0.01" name="quantity_in_stock" :value="old('quantity_in_stock', $rawMaterial->quantity_in_stock)" required />
                            <x-input-error :messages="$errors->get('quantity_in_stock')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="seller_id" :value="__('Seller')" />
                            <select id="seller_id" name="seller_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Select a seller (optional)</option>
                                @foreach($sellers as $seller)
                                    <option value="{{ $seller->id }}" {{ old('seller_id', $rawMaterial->seller_id) == $seller->id ? 'selected' : '' }}>
                                        {{ $seller->name }} - {{ $seller->formatted_gst_number }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('seller_id')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="purchase_date" :value="__('Purchase Date')" />
                            <x-text-input id="purchase_date" class="block mt-1 w-full" type="date" name="purchase_date" :value="old('purchase_date', $rawMaterial->purchase_date?->format('Y-m-d'))" />
                            <x-input-error :messages="$errors->get('purchase_date')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="purchase_price" :value="__('Purchase Price')" />
                            <x-text-input id="purchase_price" class="block mt-1 w-full" type="number" step="0.01" name="purchase_price" :value="old('purchase_price', $rawMaterial->purchase_price)" />
                            <x-input-error :messages="$errors->get('purchase_price')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="invoice" :value="__('Invoice (PDF, JPG, PNG)')" />
                            @if($rawMaterial->invoice_path)
                                <div class="mb-2">
                                    <p class="text-sm text-gray-600">Current invoice:
                                        <a href="{{ $rawMaterial->invoice_url }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $rawMaterial->invoice_file_name }}
                                        </a>
                                    </p>
                                </div>
                            @endif
                            <input id="invoice" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="file" name="invoice" accept=".pdf,.jpg,.jpeg,.png" />
                            <p class="text-sm text-gray-600 mt-1">Maximum file size: 2MB. Supported formats: PDF, JPG, PNG. Leave empty to keep current invoice.</p>
                            <x-input-error :messages="$errors->get('invoice')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('raw-materials.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">
                                Cancel
                            </a>
                            <x-primary-button class="ms-4">
                                {{ __('Update Raw Material') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
