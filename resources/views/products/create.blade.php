<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create Product') }}
            </h2>
            <a href="{{ route('products.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Products
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('products.store') }}" method="POST">
                        @csrf

                        <!-- Basic Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold mb-4">Basic Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="name" :value="__('Product Name')" />
                                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="category_id" :value="__('Category')" />
                                    <select id="category_id" name="category_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                                </div>

                                <div class="md:col-span-2">
                                    <x-input-label for="description" :value="__('Description')" />
                                    <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description') }}</textarea>
                                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="sku" :value="__('SKU')" />
                                    <x-text-input id="sku" class="block mt-1 w-full" type="text" name="sku" :value="old('sku')" />
                                    <x-input-error :messages="$errors->get('sku')" class="mt-2" />
                                    <p class="text-sm text-gray-500 mt-1">Leave empty to auto-generate</p>
                                </div>

                                <div>
                                    <x-input-label for="barcode" :value="__('Barcode')" />
                                    <x-text-input id="barcode" class="block mt-1 w-full" type="text" name="barcode" :value="old('barcode')" />
                                    <x-input-error :messages="$errors->get('barcode')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Pricing Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold mb-4">Pricing Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label for="cost_price" :value="__('Cost Price')" />
                                    <x-text-input id="cost_price" class="block mt-1 w-full" type="number" step="0.01" name="cost_price" :value="old('cost_price')" />
                                    <x-input-error :messages="$errors->get('cost_price')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="selling_price" :value="__('Selling Price')" />
                                    <x-text-input id="selling_price" class="block mt-1 w-full" type="number" step="0.01" name="selling_price" :value="old('selling_price')" />
                                    <x-input-error :messages="$errors->get('selling_price')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="wholesale_price" :value="__('Wholesale Price')" />
                                    <x-text-input id="wholesale_price" class="block mt-1 w-full" type="number" step="0.01" name="wholesale_price" :value="old('wholesale_price')" />
                                    <x-input-error :messages="$errors->get('wholesale_price')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Inventory Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold mb-4">Inventory Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                <div>
                                    <x-input-label for="unit" :value="__('Unit')" />
                                    <select id="unit" name="unit" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Select Unit</option>
                                        <option value="pieces" {{ old('unit') == 'pieces' ? 'selected' : '' }}>Pieces</option>
                                        <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kilograms</option>
                                        <option value="g" {{ old('unit') == 'g' ? 'selected' : '' }}>Grams</option>
                                        <option value="liters" {{ old('unit') == 'liters' ? 'selected' : '' }}>Liters</option>
                                        <option value="ml" {{ old('unit') == 'ml' ? 'selected' : '' }}>Milliliters</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="quantity_in_stock" :value="__('Current Stock')" />
                                    <x-text-input id="quantity_in_stock" class="block mt-1 w-full" type="number" step="0.01" name="quantity_in_stock" :value="old('quantity_in_stock', 0)" required />
                                    <x-input-error :messages="$errors->get('quantity_in_stock')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="minimum_stock_level" :value="__('Minimum Stock')" />
                                    <x-text-input id="minimum_stock_level" class="block mt-1 w-full" type="number" step="0.01" name="minimum_stock_level" :value="old('minimum_stock_level')" />
                                    <x-input-error :messages="$errors->get('minimum_stock_level')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="reorder_quantity" :value="__('Reorder Quantity')" />
                                    <x-text-input id="reorder_quantity" class="block mt-1 w-full" type="number" step="0.01" name="reorder_quantity" :value="old('reorder_quantity')" />
                                    <x-input-error :messages="$errors->get('reorder_quantity')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Physical Properties -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold mb-4">Physical Properties</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label for="weight" :value="__('Weight (kg)')" />
                                    <x-text-input id="weight" class="block mt-1 w-full" type="number" step="0.001" name="weight" :value="old('weight')" />
                                    <x-input-error :messages="$errors->get('weight')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="shelf_life_days" :value="__('Shelf Life (days)')" />
                                    <x-text-input id="shelf_life_days" class="block mt-1 w-full" type="number" name="shelf_life_days" :value="old('shelf_life_days')" />
                                    <x-input-error :messages="$errors->get('shelf_life_days')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="storage_temperature" :value="__('Storage Temperature')" />
                                    <select id="storage_temperature" name="storage_temperature" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Select Temperature</option>
                                        <option value="room_temp" {{ old('storage_temperature') == 'room_temp' ? 'selected' : '' }}>Room Temperature</option>
                                        <option value="refrigerated" {{ old('storage_temperature') == 'refrigerated' ? 'selected' : '' }}>Refrigerated</option>
                                        <option value="frozen" {{ old('storage_temperature') == 'frozen' ? 'selected' : '' }}>Frozen</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('storage_temperature')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Production & Marketing -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold mb-4">Production & Marketing</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="production_time_minutes" :value="__('Production Time (minutes)')" />
                                    <x-text-input id="production_time_minutes" class="block mt-1 w-full" type="number" name="production_time_minutes" :value="old('production_time_minutes')" />
                                    <x-input-error :messages="$errors->get('production_time_minutes')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="image_path" :value="__('Image Path')" />
                                    <x-text-input id="image_path" class="block mt-1 w-full" type="text" name="image_path" :value="old('image_path')" />
                                    <x-input-error :messages="$errors->get('image_path')" class="mt-2" />
                                </div>

                                <div class="md:col-span-2">
                                    <x-input-label for="ingredients_list" :value="__('Ingredients List')" />
                                    <textarea id="ingredients_list" name="ingredients_list" rows="2" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('ingredients_list') }}</textarea>
                                    <x-input-error :messages="$errors->get('ingredients_list')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Status & Options -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold mb-4">Status & Options</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="status" :value="__('Status')" />
                                    <select id="status" name="status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="discontinued" {{ old('status') == 'discontinued' ? 'selected' : '' }}>Discontinued</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                                </div>

                                <div class="flex items-center mt-6">
                                    <input id="is_seasonal" type="checkbox" name="is_seasonal" value="1" {{ old('is_seasonal') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <label for="is_seasonal" class="ml-2 text-sm text-gray-600">Seasonal Product</label>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('products.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Create Product') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
