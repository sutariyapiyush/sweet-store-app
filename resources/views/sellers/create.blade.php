<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Seller') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('sellers.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Left Column -->
                            <div>
                                <div class="mb-4">
                                    <x-input-label for="name" :value="__('Seller Name')" />
                                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>

                                <div class="mb-4">
                                    <x-input-label for="company_name" :value="__('Company Name')" />
                                    <x-text-input id="company_name" class="block mt-1 w-full" type="text" name="company_name" :value="old('company_name')" />
                                    <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
                                </div>

                                <div class="mb-4">
                                    <x-input-label for="contact_person" :value="__('Contact Person')" />
                                    <x-text-input id="contact_person" class="block mt-1 w-full" type="text" name="contact_person" :value="old('contact_person')" />
                                    <x-input-error :messages="$errors->get('contact_person')" class="mt-2" />
                                </div>

                                <div class="mb-4">
                                    <x-input-label for="email" :value="__('Email')" />
                                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <div class="mb-4">
                                    <x-input-label for="phone" :value="__('Phone Number')" />
                                    <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" />
                                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div>
                                <div class="mb-4">
                                    <x-input-label for="gst_number" :value="__('GST Number')" />
                                    <x-text-input id="gst_number" class="block mt-1 w-full" type="text" name="gst_number" :value="old('gst_number')" required maxlength="15" placeholder="22AAAAA0000A1Z5" />
                                    <p class="text-sm text-gray-600 mt-1">Format: 22AAAAA0000A1Z5 (15 characters)</p>
                                    <x-input-error :messages="$errors->get('gst_number')" class="mt-2" />
                                </div>

                                <div class="mb-4">
                                    <x-input-label for="pan_number" :value="__('PAN Number')" />
                                    <x-text-input id="pan_number" class="block mt-1 w-full" type="text" name="pan_number" :value="old('pan_number')" maxlength="10" placeholder="AAAAA0000A" />
                                    <p class="text-sm text-gray-600 mt-1">Format: AAAAA0000A (10 characters)</p>
                                    <x-input-error :messages="$errors->get('pan_number')" class="mt-2" />
                                </div>

                                <div class="mb-4">
                                    <x-input-label for="address" :value="__('Address')" />
                                    <textarea id="address" name="address" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('address') }}</textarea>
                                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                                </div>

                                <div class="mb-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-600">Active Seller</span>
                                    </label>
                                    <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('sellers.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">
                                Cancel
                            </a>
                            <x-primary-button class="ms-4">
                                {{ __('Create Seller') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-format GST number input
        document.getElementById('gst_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^A-Z0-9]/g, '');
            if (value.length > 15) {
                value = value.substring(0, 15);
            }
            e.target.value = value;
        });

        // Auto-format PAN number input
        document.getElementById('pan_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^A-Z0-9]/g, '');
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            e.target.value = value;
        });
    </script>
</x-app-layout>
