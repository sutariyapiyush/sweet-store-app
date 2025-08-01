<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Raw Material') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('raw-materials.store') }}">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="unit" :value="__('Unit (kg, liter, pieces, etc.)')" />
                            <x-text-input id="unit" class="block mt-1 w-full" type="text" name="unit" :value="old('unit')" required />
                            <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="quantity_in_stock" :value="__('Initial Stock Quantity')" />
                            <x-text-input id="quantity_in_stock" class="block mt-1 w-full" type="number" step="0.01" name="quantity_in_stock" :value="old('quantity_in_stock', 0)" required />
                            <x-input-error :messages="$errors->get('quantity_in_stock')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('raw-materials.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">
                                Cancel
                            </a>
                            <x-primary-button class="ms-4">
                                {{ __('Create Raw Material') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
